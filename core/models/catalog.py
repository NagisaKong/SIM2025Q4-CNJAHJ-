from django.db import models


class ServiceCategoryQuerySet(models.QuerySet):
    def active(self) -> 'ServiceCategoryQuerySet':
        return self.filter(is_active=True)

    def search(self, term: str | None) -> 'ServiceCategoryQuerySet':
        if not term:
            return self
        return self.filter(name__icontains=term)


class ServiceCategory(models.Model):
    name = models.CharField(max_length=100, unique=True)
    description = models.TextField(blank=True)
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)

    objects = ServiceCategoryQuerySet.as_manager()

    class Meta:
        ordering = ['name']

    def __str__(self) -> str:
        return self.name

    def suspend(self) -> None:
        self.is_active = False
        self.save(update_fields=['is_active'])
