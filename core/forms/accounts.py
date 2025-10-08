from __future__ import annotations

from django import forms
from django.contrib.auth import get_user_model
from django.contrib.auth.forms import UserCreationForm

from core.models import UserProfile

User = get_user_model()


class UserAccountCreateForm(UserCreationForm):
    email = forms.EmailField(required=True)
    is_active = forms.BooleanField(required=False, initial=True, label='Account active')

    class Meta(UserCreationForm.Meta):
        model = User
        fields = ('username', 'email', 'first_name', 'last_name', 'is_active')


class UserAccountUpdateForm(forms.ModelForm):
    is_active = forms.BooleanField(required=False, label='Account active')

    class Meta:
        model = User
        fields = ('username', 'email', 'first_name', 'last_name', 'is_active')


class UserProfileForm(forms.ModelForm):
    class Meta:
        model = UserProfile
        fields = ('role', 'description', 'groups', 'permissions', 'is_active')
        widgets = {
            'description': forms.Textarea(attrs={'rows': 3}),
            'groups': forms.CheckboxSelectMultiple,
            'permissions': forms.CheckboxSelectMultiple,
        }
