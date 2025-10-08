const express = require('express');
const path = require('path');
const morgan = require('morgan');
const session = require('express-session');

const { dataStore } = require('./data/sampleData');
const FlashMessageService = require('./services/FlashMessageService');
const AuthController = require('./controllers/AuthController');
const DashboardController = require('./controllers/DashboardController');
const AdminController = require('./controllers/AdminController');

class VolunteerPlatformApp {
  #dataStore;
  #app;
  #port;
  #flashService;
  #authController;
  #dashboardController;
  #adminController;

  constructor(store) {
    this.#dataStore = store;
    this.#app = express();
    this.#port = process.env.PORT || 3000;

    this.#flashService = new FlashMessageService();
    this.#authController = new AuthController({ dataStore: this.#dataStore, flash: this.#flashService });
    this.#dashboardController = new DashboardController({
      dataStore: this.#dataStore,
      flash: this.#flashService
    });
    this.#adminController = new AdminController({ dataStore: this.#dataStore, flash: this.#flashService });

    this.#configureMiddleware();
    this.#registerRoutes();
  }

  get app() {
    return this.#app;
  }

  #configureMiddleware() {
    this.#app.set('view engine', 'ejs');
    this.#app.set('views', path.join(__dirname, 'views'));

    this.#app.use(morgan('dev'));
    this.#app.use(express.static(path.join(__dirname, 'public')));
    this.#app.use(express.urlencoded({ extended: true }));

    this.#app.use(
      session({
        secret: 'csr-volunteer-secret',
        resave: false,
        saveUninitialized: false
      })
    );

    this.#app.use((req, res, next) => {
      res.locals.flash = req.session.flash || null;
      res.locals.loginPrefill = req.session.loginPrefill || null;
      delete req.session.flash;
      delete req.session.loginPrefill;
      next();
    });
  }

  #registerRoutes() {
    this.#app.get('/', this.#authController.redirectRoot);

    this.#app.get('/dashboard', this.#authController.requireAuth, this.#dashboardController.showDashboard);

    this.#app.get('/login', this.#authController.showLogin);
    this.#app.post('/login', this.#authController.login);

    this.#app.post('/logout', this.#authController.requireAuth, this.#authController.logout);

    this.#app.post(
      '/admin/accounts/create',
      this.#authController.requireAdmin,
      this.#adminController.createAccount
    );
    this.#app.post(
      '/admin/accounts/update',
      this.#authController.requireAdmin,
      this.#adminController.updateAccount
    );
    this.#app.post(
      '/admin/accounts/suspend',
      this.#authController.requireAdmin,
      this.#adminController.suspendAccount
    );

    this.#app.post(
      '/admin/profiles/create',
      this.#authController.requireAdmin,
      this.#adminController.createProfile
    );
    this.#app.post(
      '/admin/profiles/update',
      this.#authController.requireAdmin,
      this.#adminController.updateProfile
    );
    this.#app.post(
      '/admin/profiles/suspend',
      this.#authController.requireAdmin,
      this.#adminController.suspendProfile
    );

    this.#app.get('/api/pin-requests', (req, res) => {
      res.json(this.#dataStore.getPinRequests());
    });

    this.#app.get('/api/csr-history', (req, res) => {
      res.json(this.#dataStore.getCsrHistory());
    });

    this.#app.get('/api/pin-matches', (req, res) => {
      res.json(this.#dataStore.getPinMatches());
    });

    this.#app.get('/api/service-categories', (req, res) => {
      res.json(this.#dataStore.getServiceCategories());
    });

    this.#app.get('/api/reports', (req, res) => {
      res.json(this.#dataStore.getReports());
    });
  }

  start() {
    this.#app.listen(this.#port, () => {
      console.log(`CSR volunteer matching app listening on http://localhost:${this.#port}`);
    });
  }
}

const appInstance = new VolunteerPlatformApp(dataStore);

if (require.main === module) {
  appInstance.start();
}

module.exports = appInstance;
