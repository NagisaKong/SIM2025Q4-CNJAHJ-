from __future__ import annotations

import csv
from io import StringIO

from django.http import HttpResponse
from django.views.generic import FormView

from core.forms import ReportFilterForm
from core.services import ReportService

from .mixins import ManagerRoleRequiredMixin


class ReportView(ManagerRoleRequiredMixin, FormView):
    template_name = 'core/reports/report_view.html'
    form_class = ReportFilterForm

    def get_context_data(self, **kwargs):
        context = super().get_context_data(**kwargs)
        context.setdefault('rows', [])
        return context

    def form_valid(self, form):
        service = ReportService()
        start, end = form.cleaned_range()
        rows = service.generate(
            report_type=form.cleaned_data['report_type'],
            start=start,
            end=end,
        )
        context = self.get_context_data(form=form, rows=rows)
        return self.render_to_response(context)


class ReportExportView(ManagerRoleRequiredMixin, FormView):
    form_class = ReportFilterForm
    http_method_names = ['post']

    def form_valid(self, form):
        service = ReportService()
        start, end = form.cleaned_range()
        rows = service.generate(
            report_type=form.cleaned_data['report_type'],
            start=start,
            end=end,
        )
        buffer = StringIO()
        writer = csv.writer(buffer)
        writer.writerow(['Period', 'Accepted', 'Completed'])
        for row in rows:
            writer.writerow([row['period'], row['accepted'], row['completed']])
        response = HttpResponse(buffer.getvalue(), content_type='text/csv')
        response['Content-Disposition'] = 'attachment; filename="report.csv"'
        return response

    def form_invalid(self, form):
        return HttpResponse(status=400)
