from __future__ import annotations

from datetime import date

from django.contrib.auth import get_user_model

from core.models import HelpRequest, MatchRecord, ShortlistItem

User = get_user_model()


class CsrService:
    def search_requests(
        self,
        *,
        category_id: int | None = None,
        keyword: str | None = None,
        location: str | None = None,
        start_date: date | None = None,
        end_date: date | None = None,
    ):
        queryset = HelpRequest.objects.visible()
        queryset = queryset.filter_category(category_id)
        queryset = queryset.filter_location(location)
        queryset = queryset.filter_date_range(start_date, end_date)
        return queryset.search(keyword)

    def shortlist_request(self, *, csr: User, request: HelpRequest) -> ShortlistItem:
        shortlist, _ = ShortlistItem.objects.get_or_create(csr=csr, request=request)
        return shortlist

    def shortlist_for_user(self, csr: User, keyword: str | None = None):
        return ShortlistItem.objects.for_user(csr).search(keyword)

    def completed_history(
        self,
        csr: User,
        *,
        category_id: int | None = None,
        start_date: date | None = None,
        end_date: date | None = None,
    ):
        queryset = MatchRecord.objects.for_csr(csr)
        queryset = queryset.filter_category(category_id)
        return queryset.filter_date_range(start_date, end_date)
