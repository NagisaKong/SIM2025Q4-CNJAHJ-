from django.urls import path

from . import views

app_name = 'core'

urlpatterns = [
    path('', views.DashboardView.as_view(), name='dashboard'),
    # Admin
    path('admin/accounts/', views.AccountListView.as_view(), name='account_list'),
    path('admin/accounts/create/', views.AccountCreateView.as_view(), name='account_create'),
    path('admin/accounts/<int:pk>/', views.AccountDetailView.as_view(), name='account_detail'),
    path('admin/accounts/<int:pk>/edit/', views.AccountUpdateView.as_view(), name='account_update'),
    path('admin/accounts/<int:pk>/suspend/', views.AccountSuspendView.as_view(), name='account_suspend'),
    path('admin/profiles/', views.ProfileListView.as_view(), name='profile_list'),
    path('admin/profiles/<int:pk>/', views.ProfileDetailView.as_view(), name='profile_detail'),
    path('admin/profiles/<int:pk>/edit/', views.ProfileUpdateView.as_view(), name='profile_update'),
    # CSR
    path('csr/requests/', views.HelpRequestBrowseView.as_view(), name='csr_request_list'),
    path('csr/requests/<int:pk>/', views.HelpRequestDetailView.as_view(), name='csr_request_detail'),
    path('csr/requests/<int:pk>/shortlist/', views.AddShortlistView.as_view(), name='csr_request_shortlist'),
    path('csr/shortlist/', views.ShortlistListView.as_view(), name='csr_shortlist'),
    path('csr/history/', views.CsrHistoryView.as_view(), name='csr_history'),
    # PIN
    path('pin/requests/', views.PinRequestListView.as_view(), name='pin_request_list'),
    path('pin/requests/create/', views.PinRequestCreateView.as_view(), name='pin_request_create'),
    path('pin/requests/<int:pk>/edit/', views.PinRequestUpdateView.as_view(), name='pin_request_update'),
    path('pin/requests/<int:pk>/delete/', views.PinRequestDeleteView.as_view(), name='pin_request_delete'),
    path('pin/history/', views.PinHistoryView.as_view(), name='pin_history'),
    # Manager
    path('manager/categories/', views.ServiceCategoryListView.as_view(), name='category_list'),
    path('manager/categories/create/', views.ServiceCategoryCreateView.as_view(), name='category_create'),
    path('manager/categories/<int:pk>/edit/', views.ServiceCategoryUpdateView.as_view(), name='category_update'),
    path('manager/reports/', views.ReportView.as_view(), name='reports'),
    path('manager/reports/export/', views.ReportExportView.as_view(), name='reports_export'),
]
