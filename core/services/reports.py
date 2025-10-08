from __future__ import annotations

from collections import defaultdict
from django.db.models import Count
from django.db.models.functions import TruncDate

from core.models import HelpRequest, MatchRecord


class ReportRow(dict):
    period: str
    accepted: int
    completed: int


class ReportService:
    def generate(self, *, report_type: str, start, end) -> list[ReportRow]:
        period_format = {
            'daily': '%Y-%m-%d',
            'weekly': '%Y-W%W',
            'monthly': '%Y-%m',
        }[report_type]

        accepted = self._aggregate_queryset(
            HelpRequest.objects.filter(status__in=[HelpRequest.STATUS_IN_PROGRESS, HelpRequest.STATUS_COMPLETED]),
            'created_at',
            period_format,
            start,
            end,
        )
        completed = self._aggregate_queryset(
            MatchRecord.objects.all(),
            'completed_at',
            period_format,
            start,
            end,
        )

        keys = sorted(set(accepted.keys()) | set(completed.keys()))
        results: list[ReportRow] = []
        for key in keys:
            results.append(
                ReportRow(
                    period=key,
                    accepted=accepted.get(key, 0),
                    completed=completed.get(key, 0),
                )
            )
        return results

    def _aggregate_queryset(self, queryset, date_field: str, period_format: str, start, end):
        if start:
            queryset = queryset.filter(**{f'{date_field}__date__gte': start})
        if end:
            queryset = queryset.filter(**{f'{date_field}__date__lte': end})

        values = queryset.annotate(period=TruncDate(date_field))
        summary = defaultdict(int)
        for record in values.values('period').annotate(total=Count('id')):
            period = record['period']
            if period is None:
                continue
            formatted = period.strftime(period_format)
            summary[formatted] += record['total']
        return summary
