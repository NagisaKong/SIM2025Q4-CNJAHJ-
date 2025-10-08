from __future__ import annotations

from django.contrib import messages
from django.urls import reverse_lazy
from django.views.generic import CreateView, ListView, UpdateView

from core.forms import ServiceCategoryForm, ServiceCategorySearchForm
from core.models import ServiceCategory

from .mixins import ManagerRoleRequiredMixin


class ServiceCategoryListView(ManagerRoleRequiredMixin, ListView):
    model = ServiceCategory
    template_name = 'core/catalog/category_list.html'
    paginate_by = 20

    def get_queryset(self):
        form = self.get_search_form()
        queryset = ServiceCategory.objects.all()
        if form.is_valid():
            filters = form.cleaned_filters()
            queryset = queryset.search(filters['keyword'])
            if filters['is_active'] is True:
                queryset = queryset.filter(is_active=True)
            elif filters['is_active'] is False:
                queryset = queryset.filter(is_active=False)
        return queryset

    def get_context_data(self, **kwargs):
        context = super().get_context_data(**kwargs)
        context['search_form'] = self.get_search_form()
        return context

    def get_search_form(self):
        return ServiceCategorySearchForm(self.request.GET or None)


class ServiceCategoryCreateView(ManagerRoleRequiredMixin, CreateView):
    model = ServiceCategory
    template_name = 'core/catalog/category_form.html'
    form_class = ServiceCategoryForm
    success_url = reverse_lazy('core:category_list')
    title = 'Create category'

    def get_context_data(self, **kwargs):
        context = super().get_context_data(**kwargs)
        context['cancel_url'] = self.success_url
        return context

    def form_valid(self, form):
        messages.success(self.request, 'Category created successfully.')
        return super().form_valid(form)


class ServiceCategoryUpdateView(ManagerRoleRequiredMixin, UpdateView):
    model = ServiceCategory
    template_name = 'core/catalog/category_form.html'
    form_class = ServiceCategoryForm
    success_url = reverse_lazy('core:category_list')
    title = 'Update category'

    def get_context_data(self, **kwargs):
        context = super().get_context_data(**kwargs)
        context['cancel_url'] = self.success_url
        return context

    def form_valid(self, form):
        messages.success(self.request, 'Category updated successfully.')
        return super().form_valid(form)
