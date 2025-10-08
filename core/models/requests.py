from __future__ import annotations

from datetime import date

from django.contrib.auth import get_user_model
from django.db import models

from .catalog import ServiceCategory

User = get_user_model()


class HelpRequestQuerySet(models.QuerySet):
    def for_pin(self, pin: User) -> 'HelpRequestQuerySet':
        return self.filter(pin=pin)

    def visible(self) -> 'HelpRequestQuerySet':
        return self.filter(status__in=[HelpRequest.STATUS_OPEN, HelpRequest.STATUS_IN_PROGRESS])

    def search(self, term: str | None) -> 'HelpRequestQuerySet':
        if not term:
            return self
        return self.filter(title__icontains=term)

    def filter_category(self, category_id: str | None) -> 'HelpRequestQuerySet':
        if not category_id:
            return self
        return self.filter(category_id=category_id)

    def filter_location(self, location: str | None) -> 'HelpRequestQuerySet':
        if not location:
            return self
        return self.filter(location__icontains=location)

    def filter_date_range(self, start: date | None, end: date | None) -> 'HelpRequestQuerySet':
        if start:
            self = self.filter(requested_date__gte=start)
        if end:
            self = self.filter(requested_date__lte=end)
        return self

    def completed(self) -> 'HelpRequestQuerySet':
        return self.filter(status=HelpRequest.STATUS_COMPLETED)


class HelpRequest(models.Model):
    STATUS_DRAFT = 'draft'
    STATUS_OPEN = 'open'
    STATUS_IN_PROGRESS = 'in_progress'
    STATUS_COMPLETED = 'completed'
    STATUS_CHOICES = [
        (STATUS_DRAFT, 'Draft'),
        (STATUS_OPEN, 'Open'),
        (STATUS_IN_PROGRESS, 'In progress'),
        (STATUS_COMPLETED, 'Completed'),
    ]

    pin = models.ForeignKey(User, on_delete=models.CASCADE, related_name='help_requests')
    category = models.ForeignKey(ServiceCategory, on_delete=models.SET_NULL, null=True, blank=True)
    title = models.CharField(max_length=120)
    description = models.TextField()
    location = models.CharField(max_length=120)
    requested_date = models.DateField()
    status = models.CharField(max_length=20, choices=STATUS_CHOICES, default=STATUS_OPEN)
    view_count = models.PositiveIntegerField(default=0)
    shortlist_count = models.PositiveIntegerField(default=0)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    objects = HelpRequestQuerySet.as_manager()

    class Meta:
        ordering = ['-created_at']

    def __str__(self) -> str:
        return self.title

    def increment_view(self) -> None:
        HelpRequest.objects.filter(pk=self.pk).update(view_count=models.F('view_count') + 1)
        self.refresh_from_db(fields=['view_count'])

    def increment_shortlist(self) -> None:
        HelpRequest.objects.filter(pk=self.pk).update(shortlist_count=models.F('shortlist_count') + 1)
        self.refresh_from_db(fields=['shortlist_count'])


class ShortlistItemQuerySet(models.QuerySet):
    def for_user(self, user: User) -> 'ShortlistItemQuerySet':
        return self.filter(csr=user)

    def search(self, term: str | None) -> 'ShortlistItemQuerySet':
        if not term:
            return self
        return self.filter(request__title__icontains=term)


class ShortlistItem(models.Model):
    csr = models.ForeignKey(User, on_delete=models.CASCADE, related_name='shortlisted_requests')
    request = models.ForeignKey(HelpRequest, on_delete=models.CASCADE, related_name='shortlisted_by')
    created_at = models.DateTimeField(auto_now_add=True)

    objects = ShortlistItemQuerySet.as_manager()

    class Meta:
        unique_together = ('csr', 'request')
        ordering = ['-created_at']

    def __str__(self) -> str:
        return f"{self.csr.username} → {self.request.title}"

    def save(self, *args, **kwargs):
        is_new = self.pk is None
        super().save(*args, **kwargs)
        if is_new:
            self.request.increment_shortlist()


class MatchRecordQuerySet(models.QuerySet):
    def for_csr(self, csr: User) -> 'MatchRecordQuerySet':
        return self.filter(csr=csr)

    def for_pin(self, pin: User) -> 'MatchRecordQuerySet':
        return self.filter(request__pin=pin)

    def filter_category(self, category_id: str | None) -> 'MatchRecordQuerySet':
        if not category_id:
            return self
        return self.filter(request__category_id=category_id)

    def filter_date_range(self, start: date | None, end: date | None) -> 'MatchRecordQuerySet':
        if start:
            self = self.filter(completed_at__date__gte=start)
        if end:
            self = self.filter(completed_at__date__lte=end)
        return self


class MatchRecord(models.Model):
    request = models.ForeignKey(HelpRequest, on_delete=models.CASCADE, related_name='matches')
    csr = models.ForeignKey(User, on_delete=models.CASCADE, related_name='completed_matches')
    completed_at = models.DateTimeField()
    notes = models.TextField(blank=True)

    objects = MatchRecordQuerySet.as_manager()

    class Meta:
        ordering = ['-completed_at']

    def __str__(self) -> str:
        return f"{self.request.title} → {self.csr.get_full_name() or self.csr.username}"
