from __future__ import annotations

from django.contrib import messages
from django.contrib.auth import get_user_model
from django.db.models import Q
from django.shortcuts import get_object_or_404, redirect
from django.urls import reverse_lazy
from django.views.generic import DetailView, FormView, ListView, UpdateView, View

from core.forms import UserAccountCreateForm, UserAccountUpdateForm, UserProfileForm
from core.models import UserProfile
from core.services import AccountService

from .mixins import AdminRoleRequiredMixin

User = get_user_model()


class AccountListView(AdminRoleRequiredMixin, ListView):
    template_name = 'core/accounts/account_list.html'
    model = User
    paginate_by = 20

    def get_queryset(self):
        queryset = super().get_queryset().select_related('profile')
        term = self.request.GET.get('q')
        if term:
            queryset = queryset.filter(
                Q(username__icontains=term)
                | Q(email__icontains=term)
                | Q(first_name__icontains=term)
                | Q(last_name__icontains=term)
            )
        return queryset.order_by('username')


class AccountDetailView(AdminRoleRequiredMixin, DetailView):
    template_name = 'core/accounts/account_detail.html'
    model = User


class AccountCreateView(AdminRoleRequiredMixin, FormView):
    template_name = 'core/accounts/account_form.html'
    form_class = UserAccountCreateForm
    success_url = reverse_lazy('core:account_list')

    def get_context_data(self, **kwargs):
        context = super().get_context_data(**kwargs)
        context.setdefault('profile_form', UserProfileForm(self.request.POST or None))
        return context

    def form_valid(self, form):
        profile_form = UserProfileForm(self.request.POST)
        if not profile_form.is_valid():
            return self.form_invalid(form)
        service = AccountService()
        service.create_account(
            username=form.cleaned_data['username'],
            email=form.cleaned_data['email'],
            first_name=form.cleaned_data['first_name'],
            last_name=form.cleaned_data['last_name'],
            password=form.cleaned_data['password1'],
            role=profile_form.cleaned_data['role'],
            description=profile_form.cleaned_data['description'],
            permissions=profile_form.cleaned_data['permissions'],
            groups=profile_form.cleaned_data['groups'],
            is_active=form.cleaned_data['is_active'],
            profile_active=profile_form.cleaned_data['is_active'],
        )
        messages.success(self.request, 'Account created successfully.')
        return super().form_valid(form)


class AccountUpdateView(AdminRoleRequiredMixin, UpdateView):
    template_name = 'core/accounts/account_form.html'
    model = User
    form_class = UserAccountUpdateForm
    success_url = reverse_lazy('core:account_list')


    def get_context_data(self, **kwargs):
        context = super().get_context_data(**kwargs)
        profile = self.get_object().profile
        context.setdefault('profile_form', UserProfileForm(self.request.POST or None, instance=profile))
        context['update'] = True
        return context

    def form_valid(self, form):
        profile_form = UserProfileForm(self.request.POST, instance=self.get_object().profile)
        if not profile_form.is_valid():
            return self.form_invalid(form)
        user = form.save()
        profile_form.save()
        user.groups.set(profile_form.cleaned_data['groups'])
        user.user_permissions.set(profile_form.cleaned_data['permissions'])
        messages.success(self.request, 'Account updated successfully.')
        return super().form_valid(form)


class AccountSuspendView(AdminRoleRequiredMixin, View):
    def post(self, request, *args, **kwargs):
        user = get_object_or_404(User, pk=kwargs['pk'])
        service = AccountService()
        if user.is_active:
            service.suspend_account(user, performed_by=request.user)
            messages.warning(request, 'User account suspended.')
        else:
            service.activate_account(user, performed_by=request.user)
            messages.success(request, 'User account reactivated.')
        return redirect('core:account_detail', pk=user.pk)


class ProfileListView(AdminRoleRequiredMixin, ListView):
    template_name = 'core/accounts/profile_list.html'
    model = UserProfile
    paginate_by = 20

    def get_queryset(self):
        queryset = super().get_queryset().select_related('user')
        term = self.request.GET.get('q')
        if term:
            queryset = queryset.filter(
                Q(user__username__icontains=term) | Q(description__icontains=term) | Q(role__icontains=term)
            )
        return queryset


class ProfileDetailView(AdminRoleRequiredMixin, DetailView):
    template_name = 'core/accounts/profile_detail.html'
    model = UserProfile


class ProfileUpdateView(AdminRoleRequiredMixin, UpdateView):
    template_name = 'core/accounts/profile_form.html'
    model = UserProfile
    form_class = UserProfileForm
    success_url = reverse_lazy('core:profile_list')

    def form_valid(self, form):
        messages.success(self.request, 'Profile updated successfully.')
        return super().form_valid(form)
