from django.contrib import admin

from core.models import AccountAuditLog, HelpRequest, MatchRecord, ServiceCategory, ShortlistItem, UserProfile


@admin.register(UserProfile)
class UserProfileAdmin(admin.ModelAdmin):
    list_display = ('user', 'role', 'is_active')
    list_filter = ('role', 'is_active')
    search_fields = ('user__username', 'user__email')


@admin.register(ServiceCategory)
class ServiceCategoryAdmin(admin.ModelAdmin):
    list_display = ('name', 'is_active', 'created_at')
    list_filter = ('is_active',)
    search_fields = ('name',)


@admin.register(HelpRequest)
class HelpRequestAdmin(admin.ModelAdmin):
    list_display = ('title', 'pin', 'status', 'requested_date')
    list_filter = ('status', 'requested_date')
    search_fields = ('title', 'pin__username')


@admin.register(ShortlistItem)
class ShortlistItemAdmin(admin.ModelAdmin):
    list_display = ('csr', 'request', 'created_at')
    search_fields = ('csr__username', 'request__title')


@admin.register(MatchRecord)
class MatchRecordAdmin(admin.ModelAdmin):
    list_display = ('request', 'csr', 'completed_at')
    list_filter = ('completed_at',)


@admin.register(AccountAuditLog)
class AccountAuditLogAdmin(admin.ModelAdmin):
    list_display = ('user', 'action', 'performed_by', 'performed_at')
    list_filter = ('action', 'performed_at')
    search_fields = ('user__username', 'performed_by__username')
