from django.contrib.auth import get_user_model
from django.test import TestCase

from core.models import ServiceCategory, UserProfile


class UserProfileModelTests(TestCase):
    def test_string_representation(self):
        user = get_user_model().objects.create_user('alice', 'alice@example.com', 'pass1234')
        profile = UserProfile.objects.create(user=user, role=UserProfile.ROLE_ADMIN)
        self.assertIn('alice', str(profile))

    def test_suspend_updates_flag(self):
        user = get_user_model().objects.create_user('bob', 'bob@example.com', 'pass1234')
        profile = UserProfile.objects.create(user=user, role=UserProfile.ROLE_PIN)
        profile.suspend()
        self.assertFalse(UserProfile.objects.get(pk=profile.pk).is_active)


class ServiceCategoryQueryTests(TestCase):
    def test_active_filter(self):
        active = ServiceCategory.objects.create(name='Medical', is_active=True)
        inactive = ServiceCategory.objects.create(name='Transport', is_active=False)
        self.assertIn(active, ServiceCategory.objects.active())
        self.assertNotIn(inactive, ServiceCategory.objects.active())
