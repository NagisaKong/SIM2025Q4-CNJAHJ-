class DashboardController {
  constructor({ dataStore, flash }) {
    this.dataStore = dataStore;
    this.flash = flash;

    this.showDashboard = this.showDashboard.bind(this);
  }

  showDashboard(req, res) {
    const accountKeyword = req.query.accountSearch || '';
    const profileKeyword = req.query.profileSearch || '';

    res.render('index', {
      roles: this.dataStore.getRoles(),
      pinRequests: this.dataStore.getPinRequests(),
      csrShortlist: this.dataStore.getCsrShortlist(),
      csrHistory: this.dataStore.getCsrHistory(),
      pinMetrics: this.dataStore.getPinMetrics(),
      pinMatches: this.dataStore.getPinMatches(),
      serviceCategories: this.dataStore.getServiceCategories(),
      requestStatuses: this.dataStore.getRequestStatuses(),
      reports: this.dataStore.getReports(),
      accounts: this.dataStore.searchUserAccounts(accountKeyword),
      profiles: this.dataStore.searchUserProfiles(profileKeyword),
      volunteerOpportunities: this.dataStore.getVolunteerOpportunities(),
      accountKeyword,
      profileKeyword,
      currentUser: req.session.user,
      flash: this.flash.consumeFlash(res)
    });
  }
}

module.exports = DashboardController;
