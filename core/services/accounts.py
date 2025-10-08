from __future__ import annotations

from dataclasses import dataclass
from typing import Iterable

from django.contrib.auth import get_user_model
from django.contrib.auth.models import Permission
from django.db import transaction

from core.models import AccountAuditLog, UserProfile

User = get_user_model()


@dataclass
class AccountResult:
    user: User
    profile: UserProfile


class AccountService:
    @transaction.atomic
    def create_account(
        self,
        *,
        username: str,
        email: str,
        first_name: str,
        last_name: str,
        password: str,
        role: str,
        description: str = '',
        permissions: Iterable[Permission] | None = None,
        groups: Iterable = (),
        is_active: bool = True,
        profile_active: bool = True,
    ) -> AccountResult:
        user = User.objects.create_user(
            username=username,
            email=email,
            first_name=first_name,
            last_name=last_name,
            password=password,
            is_active=is_active,
        )
        profile = UserProfile.objects.create(
            user=user,
            role=role,
            description=description,
            is_active=profile_active,
        )
        permission_values = permissions if permissions is not None else []
        group_values = groups if groups is not None else []
        profile.permissions.set(permission_values)
        user.user_permissions.set(permission_values)
        profile.groups.set(group_values)
        user.groups.set(group_values)
        return AccountResult(user=user, profile=profile)

    @transaction.atomic
    def update_profile(self, profile: UserProfile, *, description: str, is_active: bool, role: str) -> UserProfile:
        profile.description = description
        profile.is_active = is_active
        profile.role = role
        profile.save()
        return profile

    @transaction.atomic
    def suspend_account(self, user: User, *, performed_by: User | None = None) -> None:
        user.is_active = False
        user.save(update_fields=['is_active'])
        AccountAuditLog.objects.create(user=user, action=AccountAuditLog.ACTION_SUSPEND, performed_by=performed_by)
        if hasattr(user, 'profile'):
            user.profile.suspend()

    @transaction.atomic
    def activate_account(self, user: User, *, performed_by: User | None = None) -> None:
        user.is_active = True
        user.save(update_fields=['is_active'])
        AccountAuditLog.objects.create(user=user, action=AccountAuditLog.ACTION_ACTIVATE, performed_by=performed_by)
        if hasattr(user, 'profile'):
            user.profile.activate()
