from datetime import date

from django.contrib.auth import get_user_model
from django.test import Client, TestCase
from django.urls import reverse

from core.models import HelpRequest, ServiceCategory, UserProfile

User = get_user_model()


class ViewAccessTests(TestCase):
    def setUp(self):
        self.admin = User.objects.create_user('admin', 'admin@example.com', 'pass1234')
        UserProfile.objects.create(user=self.admin, role=UserProfile.ROLE_ADMIN)
        self.csr = User.objects.create_user('csr', 'csr@example.com', 'pass1234')
        UserProfile.objects.create(user=self.csr, role=UserProfile.ROLE_CSR)
        self.pin = User.objects.create_user('pin', 'pin@example.com', 'pass1234')
        UserProfile.objects.create(user=self.pin, role=UserProfile.ROLE_PIN)
        self.client = Client()

    def test_admin_access_accounts(self):
        self.client.login(username='admin', password='pass1234')
        response = self.client.get(reverse('core:account_list'))
        self.assertEqual(response.status_code, 200)

    def test_csr_cannot_access_admin(self):
        self.client.login(username='csr', password='pass1234')
        response = self.client.get(reverse('core:account_list'))
        self.assertEqual(response.status_code, 302)

    def test_pin_request_creation_flow(self):
        category = ServiceCategory.objects.create(name='Medical')
        self.client.login(username='pin', password='pass1234')
        response = self.client.post(
            reverse('core:pin_request_create'),
            {
                'category': category.pk,
                'title': 'Hospital escort',
                'description': 'Need assistance visiting hospital',
                'location': 'Downtown',
                'requested_date': date.today(),
                'status': HelpRequest.STATUS_OPEN,
            },
            follow=True,
        )
        self.assertEqual(response.status_code, 200)
        self.assertEqual(HelpRequest.objects.filter(pin=self.pin).count(), 1)

    def test_csr_shortlist_flow(self):
        category = ServiceCategory.objects.create(name='Mobility')
        help_request = HelpRequest.objects.create(
            pin=self.pin,
            category=category,
            title='Wheelchair support',
            description='Assist with wheelchair transfer',
            location='City Center',
            requested_date=date.today(),
        )
        self.client.login(username='csr', password='pass1234')
        response = self.client.post(reverse('core:csr_request_shortlist', args=[help_request.pk]), follow=True)
        self.assertEqual(response.status_code, 200)
        help_request.refresh_from_db()
        self.assertEqual(help_request.shortlist_count, 1)
