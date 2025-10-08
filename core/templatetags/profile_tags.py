from django import template
from django.core.exceptions import ObjectDoesNotExist

register = template.Library()


@register.filter
def profile_role(user):
    try:
        return user.profile.get_role_display()
    except ObjectDoesNotExist:
        return '—'


@register.filter
def profile_status(user):
    try:
        return 'Active' if user.profile.is_active else 'Suspended'
    except ObjectDoesNotExist:
        return '—'
