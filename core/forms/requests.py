from __future__ import annotations

from django import forms

from core.models import HelpRequest, ServiceCategory


class HelpRequestForm(forms.ModelForm):
    class Meta:
        model = HelpRequest
        fields = ('category', 'title', 'description', 'location', 'requested_date', 'status')
        widgets = {
            'requested_date': forms.DateInput(attrs={'type': 'date'}),
            'description': forms.Textarea(attrs={'rows': 4}),
        }


class BaseCategoryForm(forms.Form):
    category = forms.ModelChoiceField(queryset=ServiceCategory.objects.none(), required=False)

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.fields['category'].queryset = ServiceCategory.objects.all()


class HelpRequestSearchForm(BaseCategoryForm):
    keyword = forms.CharField(required=False)
    location = forms.CharField(required=False)
    start_date = forms.DateField(required=False, widget=forms.DateInput(attrs={'type': 'date'}))
    end_date = forms.DateField(required=False, widget=forms.DateInput(attrs={'type': 'date'}))


class ShortlistSearchForm(forms.Form):
    keyword = forms.CharField(required=False)


class MatchHistorySearchForm(BaseCategoryForm):
    start_date = forms.DateField(required=False, widget=forms.DateInput(attrs={'type': 'date'}))
    end_date = forms.DateField(required=False, widget=forms.DateInput(attrs={'type': 'date'}))
