from __future__ import annotations

from django.contrib.auth import get_user_model
from django.contrib.auth.models import Group, Permission
from django.db import models

User = get_user_model()


class UserProfile(models.Model):
    ROLE_ADMIN = 'admin'
    ROLE_CSR = 'csr'
    ROLE_PIN = 'pin'
    ROLE_MANAGER = 'manager'
    ROLE_CHOICES = [
        (ROLE_ADMIN, 'User Admin'),
        (ROLE_CSR, 'CSR Representative'),
        (ROLE_PIN, 'Person in Need'),
        (ROLE_MANAGER, 'Platform Manager'),
    ]

    user = models.OneToOneField(User, on_delete=models.CASCADE, related_name='profile')
    role = models.CharField(max_length=20, choices=ROLE_CHOICES)
    description = models.TextField(blank=True)
    groups = models.ManyToManyField(Group, blank=True, related_name='profile_members')
    permissions = models.ManyToManyField(Permission, blank=True, related_name='profile_permissions')
    is_active = models.BooleanField(default=True)

    class Meta:
        ordering = ['user__username']

    def __str__(self) -> str:
        return f"{self.user.get_full_name() or self.user.username} ({self.get_role_display()})"

    def suspend(self) -> None:
        self.is_active = False
        self.save(update_fields=['is_active'])

    def activate(self) -> None:
        self.is_active = True
        self.save(update_fields=['is_active'])

    @property
    def is_admin(self) -> bool:
        return self.role == self.ROLE_ADMIN

    @property
    def is_csr(self) -> bool:
        return self.role == self.ROLE_CSR

    @property
    def is_pin(self) -> bool:
        return self.role == self.ROLE_PIN

    @property
    def is_manager(self) -> bool:
        return self.role == self.ROLE_MANAGER


class AccountAuditLog(models.Model):
    ACTION_SUSPEND = 'suspend'
    ACTION_ACTIVATE = 'activate'
    ACTION_CHOICES = [
        (ACTION_SUSPEND, 'Suspended'),
        (ACTION_ACTIVATE, 'Reactivated'),
    ]

    user = models.ForeignKey(User, on_delete=models.CASCADE)
    action = models.CharField(max_length=20, choices=ACTION_CHOICES)
    performed_by = models.ForeignKey(User, on_delete=models.SET_NULL, null=True, related_name='performed_account_actions')
    performed_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ['-performed_at']

    def __str__(self) -> str:
        return f"{self.user.username} - {self.get_action_display()}" \
            f" by {self.performed_by or 'system'} on {self.performed_at:%Y-%m-%d}"
