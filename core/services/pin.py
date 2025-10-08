from __future__ import annotations

from datetime import date

from django.contrib.auth import get_user_model

from core.models import HelpRequest, MatchRecord

User = get_user_model()


class PinService:
    def my_requests(self, pin: User, keyword: str | None = None):
        queryset = HelpRequest.objects.for_pin(pin)
        return queryset.search(keyword)

    def create_request(
        self,
        *,
        pin: User,
        category_id: int | None,
        title: str,
        description: str,
        location: str,
        requested_date,
        status: str,
    ) -> HelpRequest:
        request = HelpRequest.objects.create(
            pin=pin,
            category_id=category_id,
            title=title,
            description=description,
            location=location,
            requested_date=requested_date,
            status=status,
        )
        return request

    def update_request(self, request: HelpRequest, **fields) -> HelpRequest:
        for attr, value in fields.items():
            setattr(request, attr, value)
        request.save()
        return request

    def delete_request(self, request: HelpRequest) -> None:
        request.delete()

    def completed_history(
        self,
        pin: User,
        *,
        category_id: int | None = None,
        start_date: date | None = None,
        end_date: date | None = None,
    ):
        queryset = MatchRecord.objects.for_pin(pin)
        queryset = queryset.filter_category(category_id)
        return queryset.filter_date_range(start_date, end_date)
