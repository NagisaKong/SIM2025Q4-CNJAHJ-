from .dashboard import DashboardView
from .accounts import (
    AccountCreateView,
    AccountListView,
    AccountDetailView,
    AccountUpdateView,
    AccountSuspendView,
    ProfileListView,
    ProfileDetailView,
    ProfileUpdateView,
)
from .csr import (
    HelpRequestBrowseView,
    HelpRequestDetailView,
    ShortlistListView,
    AddShortlistView,
    CsrHistoryView,
)
from .pin import (
    PinRequestListView,
    PinRequestCreateView,
    PinRequestUpdateView,
    PinRequestDeleteView,
    PinHistoryView,
)
from .catalog import ServiceCategoryListView, ServiceCategoryCreateView, ServiceCategoryUpdateView
from .reports import ReportView, ReportExportView

__all__ = [
    'AccountCreateView',
    'AccountDetailView',
    'AccountListView',
    'AccountSuspendView',
    'AccountUpdateView',
    'AddShortlistView',
    'CsrHistoryView',
    'DashboardView',
    'HelpRequestBrowseView',
    'HelpRequestDetailView',
    'PinHistoryView',
    'PinRequestCreateView',
    'PinRequestDeleteView',
    'PinRequestListView',
    'PinRequestUpdateView',
    'ProfileDetailView',
    'ProfileListView',
    'ProfileUpdateView',
    'ReportExportView',
    'ReportView',
    'ServiceCategoryCreateView',
    'ServiceCategoryListView',
    'ServiceCategoryUpdateView',
    'ShortlistListView',
]
