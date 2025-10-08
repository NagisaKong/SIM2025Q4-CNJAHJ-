from __future__ import annotations

from django.contrib import messages
from django.shortcuts import get_object_or_404, redirect
from django.views.generic import DetailView, ListView, View

from core.forms import HelpRequestSearchForm, MatchHistorySearchForm, ShortlistSearchForm
from core.models import HelpRequest, ShortlistItem
from core.services import CsrService

from .mixins import CsrRoleRequiredMixin


class HelpRequestBrowseView(CsrRoleRequiredMixin, ListView):
    model = HelpRequest
    template_name = 'core/csr/request_list.html'
    paginate_by = 10

    def get_queryset(self):
        form = self.get_filter_form()
        if form.is_valid():
            service = CsrService()
            return service.search_requests(
                category_id=form.cleaned_data['category'].pk if form.cleaned_data['category'] else None,
                keyword=form.cleaned_data['keyword'],
                location=form.cleaned_data['location'],
                start_date=form.cleaned_data['start_date'],
                end_date=form.cleaned_data['end_date'],
            )
        return HelpRequest.objects.visible()

    def get_context_data(self, **kwargs):
        context = super().get_context_data(**kwargs)
        context['filter_form'] = self.get_filter_form()
        return context

    def get_filter_form(self):
        if not hasattr(self, "_filter_form"):
            self._filter_form = HelpRequestSearchForm(self.request.GET or None)
        return self._filter_form


class HelpRequestDetailView(CsrRoleRequiredMixin, DetailView):
    model = HelpRequest
    template_name = 'core/csr/request_detail.html'

    def get_object(self, queryset=None):
        obj = super().get_object(queryset)
        obj.increment_view()
        return obj


class AddShortlistView(CsrRoleRequiredMixin, View):
    def post(self, request, *args, **kwargs):
        help_request = get_object_or_404(HelpRequest, pk=kwargs['pk'])
        service = CsrService()
        service.shortlist_request(csr=request.user, request=help_request)
        messages.success(request, 'Added to shortlist.')
        return redirect('core:csr_request_detail', pk=help_request.pk)


class ShortlistListView(CsrRoleRequiredMixin, ListView):
    model = ShortlistItem
    template_name = 'core/csr/shortlist_list.html'
    paginate_by = 10

    def get_queryset(self):
        form = self.get_search_form()
        keyword = form.cleaned_data['keyword'] if form.is_valid() else None
        service = CsrService()
        return service.shortlist_for_user(self.request.user, keyword)

    def get_context_data(self, **kwargs):
        context = super().get_context_data(**kwargs)
        context['search_form'] = self.get_search_form()
        return context

    def get_search_form(self):
        if not hasattr(self, "_search_form"):
            self._search_form = ShortlistSearchForm(self.request.GET or None)
        return self._search_form


class CsrHistoryView(CsrRoleRequiredMixin, ListView):
    template_name = 'core/csr/history_list.html'
    paginate_by = 10

    def get_queryset(self):
        form = self.get_filter_form()
        if form.is_valid():
            service = CsrService()
            category = form.cleaned_data['category']
            return service.completed_history(
                self.request.user,
                category_id=category.pk if category else None,
                start_date=form.cleaned_data['start_date'],
                end_date=form.cleaned_data['end_date'],
            )
        return self.request.user.completed_matches.all()

    def get_context_data(self, **kwargs):
        context = super().get_context_data(**kwargs)
        context['filter_form'] = self.get_filter_form()
        return context

    def get_filter_form(self):
        if not hasattr(self, "_history_form"):
            self._history_form = MatchHistorySearchForm(self.request.GET or None)
        return self._history_form
