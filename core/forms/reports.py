from __future__ import annotations

from django import forms


class ReportFilterForm(forms.Form):
    REPORT_DAILY = 'daily'
    REPORT_WEEKLY = 'weekly'
    REPORT_MONTHLY = 'monthly'
    REPORT_CHOICES = [
        (REPORT_DAILY, 'Daily'),
        (REPORT_WEEKLY, 'Weekly'),
        (REPORT_MONTHLY, 'Monthly'),
    ]

    report_type = forms.ChoiceField(choices=REPORT_CHOICES, initial=REPORT_DAILY)
    start_date = forms.DateField(required=False, widget=forms.DateInput(attrs={'type': 'date'}))
    end_date = forms.DateField(required=False, widget=forms.DateInput(attrs={'type': 'date'}))

    def cleaned_range(self):
        return self.cleaned_data.get('start_date'), self.cleaned_data.get('end_date')
