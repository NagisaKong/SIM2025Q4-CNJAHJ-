from __future__ import annotations

from django.contrib import messages
from django.contrib.auth.mixins import LoginRequiredMixin, UserPassesTestMixin
from django.shortcuts import redirect

from core.models import UserProfile


class RoleRequiredMixin(LoginRequiredMixin, UserPassesTestMixin):
    allowed_roles: tuple[str, ...] = ()

    def test_func(self):
        try:
            profile: UserProfile = self.request.user.profile
        except UserProfile.DoesNotExist:  # pragma: no cover - guarded by onboarding
            return False
        if not profile.is_active:
            messages.error(self.request, 'Your profile is suspended.')
            return False
        return profile.role in self.allowed_roles

    def handle_no_permission(self):
        if self.request.user.is_authenticated:
            messages.error(self.request, 'You do not have access to this page.')
            return redirect('core:dashboard')
        return super().handle_no_permission()


class AdminRoleRequiredMixin(RoleRequiredMixin):
    allowed_roles = (UserProfile.ROLE_ADMIN,)


class CsrRoleRequiredMixin(RoleRequiredMixin):
    allowed_roles = (UserProfile.ROLE_CSR,)


class PinRoleRequiredMixin(RoleRequiredMixin):
    allowed_roles = (UserProfile.ROLE_PIN,)


class ManagerRoleRequiredMixin(RoleRequiredMixin):
    allowed_roles = (UserProfile.ROLE_MANAGER,)


class BreadcrumbMixin:
    breadcrumbs: list[tuple[str, str]] = []

    def get_context_data(self, **kwargs):
        context = super().get_context_data(**kwargs)
        context['breadcrumbs'] = self.get_breadcrumbs()
        return context

    def get_breadcrumbs(self):
        return self.breadcrumbs
