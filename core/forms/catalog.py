from django import forms

from core.models import ServiceCategory


class ServiceCategoryForm(forms.ModelForm):
    class Meta:
        model = ServiceCategory
        fields = ('name', 'description', 'is_active')
        widgets = {'description': forms.Textarea(attrs={'rows': 3})}


class ServiceCategorySearchForm(forms.Form):
    STATUS_CHOICES = [
        ('', 'All'),
        ('true', 'Active'),
        ('false', 'Suspended'),
    ]

    keyword = forms.CharField(required=False)
    is_active = forms.ChoiceField(required=False, choices=STATUS_CHOICES)

    def cleaned_filters(self) -> dict[str, bool | str | None]:
        data = self.cleaned_data
        choice = data.get('is_active')
        if choice == 'true':
            active = True
        elif choice == 'false':
            active = False
        else:
            active = None
        return {'keyword': data.get('keyword'), 'is_active': active}
