from __future__ import annotations

from django.contrib import messages
from django.urls import reverse_lazy
from django.views.generic import CreateView, DeleteView, ListView, UpdateView

from core.forms import HelpRequestForm, HelpRequestSearchForm, MatchHistorySearchForm
from core.models import HelpRequest, MatchRecord
from core.services import PinService

from .mixins import PinRoleRequiredMixin


class PinRequestListView(PinRoleRequiredMixin, ListView):
    model = HelpRequest
    template_name = 'core/pin/request_list.html'
    paginate_by = 10

    def get_queryset(self):
        form = self.get_search_form()
        keyword = form.cleaned_data['keyword'] if form.is_valid() else None
        service = PinService()
        return service.my_requests(self.request.user, keyword)

    def get_context_data(self, **kwargs):
        context = super().get_context_data(**kwargs)
        context['search_form'] = self.get_search_form()
        return context

    def get_search_form(self):
        if not hasattr(self, "_search_form"):
            self._search_form = HelpRequestSearchForm(self.request.GET or None)
        return self._search_form


class PinRequestCreateView(PinRoleRequiredMixin, CreateView):
    model = HelpRequest
    template_name = 'core/pin/request_form.html'
    form_class = HelpRequestForm
    success_url = reverse_lazy('core:pin_request_list')

    def form_valid(self, form):
        form.instance.pin = self.request.user
        messages.success(self.request, 'Request created successfully.')
        return super().form_valid(form)


class PinRequestUpdateView(PinRoleRequiredMixin, UpdateView):
    model = HelpRequest
    template_name = 'core/pin/request_form.html'
    form_class = HelpRequestForm
    success_url = reverse_lazy('core:pin_request_list')

    def get_queryset(self):
        return HelpRequest.objects.for_pin(self.request.user)

    def form_valid(self, form):
        messages.success(self.request, 'Request updated successfully.')
        return super().form_valid(form)


class PinRequestDeleteView(PinRoleRequiredMixin, DeleteView):
    model = HelpRequest
    template_name = 'core/pin/request_confirm_delete.html'
    success_url = reverse_lazy('core:pin_request_list')

    def get_queryset(self):
        return HelpRequest.objects.for_pin(self.request.user)

    def delete(self, request, *args, **kwargs):
        messages.success(request, 'Request removed.')
        return super().delete(request, *args, **kwargs)


class PinHistoryView(PinRoleRequiredMixin, ListView):
    template_name = 'core/pin/history_list.html'
    paginate_by = 10

    def get_queryset(self):
        form = self.get_filter_form()
        if form.is_valid():
            service = PinService()
            category = form.cleaned_data['category']
            return service.completed_history(
                self.request.user,
                category_id=category.pk if category else None,
                start_date=form.cleaned_data['start_date'],
                end_date=form.cleaned_data['end_date'],
            )
        return MatchRecord.objects.for_pin(self.request.user)

    def get_context_data(self, **kwargs):
        context = super().get_context_data(**kwargs)
        context['filter_form'] = self.get_filter_form()
        return context

    def get_filter_form(self):
        if not hasattr(self, "_history_form"):
            self._history_form = MatchHistorySearchForm(self.request.GET or None)
        return self._history_form
