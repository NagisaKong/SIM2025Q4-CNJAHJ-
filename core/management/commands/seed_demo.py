from __future__ import annotations

import random
from datetime import timedelta

from django.contrib.auth import get_user_model
from django.contrib.auth.models import Group
from django.core.management.base import BaseCommand
from django.db import transaction
from django.utils import timezone

from core.models import HelpRequest, MatchRecord, ServiceCategory, ShortlistItem, UserProfile

User = get_user_model()


class Command(BaseCommand):
    help = 'Seed demo data for the CSR platform'

    @transaction.atomic
    def handle(self, *args, **options):
        self.stdout.write('Creating groups and permissions...')
        groups = {
            'User Administrators': UserProfile.ROLE_ADMIN,
            'CSR Representatives': UserProfile.ROLE_CSR,
            'Persons in Need': UserProfile.ROLE_PIN,
            'Platform Managers': UserProfile.ROLE_MANAGER,
        }
        for name in groups:
            Group.objects.get_or_create(name=name)

        self.stdout.write('Creating service categories...')
        categories = [
            ServiceCategory.objects.get_or_create(name=label)[0]
            for label in ['Medical Escort', 'Wheelchair Support', 'Elderly Care', 'Home Repair', 'Education']
        ]

        self.stdout.write('Creating users and profiles...')
        roles = [
            (UserProfile.ROLE_ADMIN, 'admin'),
            (UserProfile.ROLE_MANAGER, 'manager'),
            (UserProfile.ROLE_CSR, 'csr'),
            (UserProfile.ROLE_PIN, 'pin'),
        ]
        created_users = []
        for role, prefix in roles:
            for index in range(1, 6):
                username = f'{prefix}{index}'
                user, _ = User.objects.get_or_create(
                    username=username,
                    defaults={
                        'email': f'{username}@example.com',
                        'first_name': prefix.capitalize(),
                        'last_name': str(index),
                    },
                )
                user.set_password('Passw0rd!')
                user.is_active = True
                user.save()
                profile, _ = UserProfile.objects.get_or_create(user=user, defaults={'role': role, 'is_active': True})
                group_name = {
                    UserProfile.ROLE_ADMIN: 'User Administrators',
                    UserProfile.ROLE_MANAGER: 'Platform Managers',
                    UserProfile.ROLE_CSR: 'CSR Representatives',
                    UserProfile.ROLE_PIN: 'Persons in Need',
                }[role]
                profile.groups.add(Group.objects.get(name=group_name))
                created_users.append((role, user))

        self.stdout.write('Creating help requests...')
        pin_users = [user for role, user in created_users if role == UserProfile.ROLE_PIN]
        csr_users = [user for role, user in created_users if role == UserProfile.ROLE_CSR]
        help_requests = []
        for pin in pin_users:
            for _ in range(6):
                category = random.choice(categories)
                request = HelpRequest.objects.create(
                    pin=pin,
                    category=category,
                    title=f'{category.name} assistance for {pin.first_name} {pin.last_name}',
                    description='Support required for community service engagement.',
                    location='Central District',
                    requested_date=timezone.now().date() + timedelta(days=random.randint(0, 14)),
                    status=random.choice([HelpRequest.STATUS_OPEN, HelpRequest.STATUS_IN_PROGRESS, HelpRequest.STATUS_COMPLETED]),
                )
                help_requests.append(request)

        self.stdout.write('Creating shortlist and matches...')
        for csr in csr_users:
            for request in random.sample(help_requests, min(5, len(help_requests))):
                ShortlistItem.objects.get_or_create(csr=csr, request=request)
                if request.status == HelpRequest.STATUS_COMPLETED:
                    MatchRecord.objects.get_or_create(
                        request=request,
                        csr=csr,
                        defaults={'completed_at': timezone.now() - timedelta(days=random.randint(1, 7)), 'notes': 'Completed successfully.'},
                    )

        self.stdout.write(self.style.SUCCESS('Demo data created. Default password: Passw0rd!'))
