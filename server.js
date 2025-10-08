// Application entry point: encapsulate the Express setup inside an
// object-oriented wrapper so that middleware, routes, and configuration
// remain cohesive and easy to extend.
const express = require('express');
const path = require('path');
const morgan = require('morgan');
const session = require('express-session');

const { dataStore } = require('./data/sampleData');

class VolunteerPlatformApp {
  constructor(store) {
    this.dataStore = store;
    this.app = express();
    this.port = process.env.PORT || 3000;

    // Ensure middleware methods have the correct `this` binding when used as
    // route handlers.
    this.requireAuth = this.requireAuth.bind(this);
    this.requireAdmin = this.requireAdmin.bind(this);

    this.configureMiddleware();
    this.registerRoutes();
  }

  configureMiddleware() {
    this.app.set('view engine', 'ejs');
    this.app.set('views', path.join(__dirname, 'views'));

    this.app.use(morgan('dev'));
    this.app.use(express.static(path.join(__dirname, 'public')));
    this.app.use(express.urlencoded({ extended: true }));

    this.app.use(
      session({
        secret: 'csr-volunteer-secret',
        resave: false,
        saveUninitialized: false
      })
    );

    // Middleware: expose flash messages and login prefill data to templates.
    this.app.use((req, res, next) => {
      res.locals.flash = req.session.flash || null;
      res.locals.loginPrefill = req.session.loginPrefill || null;
      delete req.session.flash;
      delete req.session.loginPrefill;
      next();
    });
  }

  setFlash(req, type, message) {
    req.session.flash = { type, message };
  }

  requireAuth(req, res, next) {
    if (!req.session.user) {
      this.setFlash(req, 'error', 'Please sign in before accessing the dashboard.');
      return res.redirect('/login');
    }
    next();
  }

  requireAdmin(req, res, next) {
    if (!req.session.user || req.session.user.role !== 'User Administrator') {
      this.setFlash(req, 'error', 'Only user administrators can perform this action.');
      return res.redirect('/dashboard');
    }
    next();
  }

  registerRoutes() {
    this.app.get('/', (req, res) => {
      if (req.session.user) {
        return res.redirect('/dashboard');
      }
      res.redirect('/login');
    });

    this.app.get('/dashboard', this.requireAuth, (req, res) => {
      const accountKeyword = req.query.accountSearch || '';
      const profileKeyword = req.query.profileSearch || '';
      const flash = res.locals.flash || null;

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
        flash
      });
    });

    this.app.get('/login', (req, res) => {
      if (req.session.user) {
        return res.redirect('/dashboard');
      }
      const loginPrefill = res.locals.loginPrefill || {};
      const defaultRole = this.dataStore.getRoles().length ? this.dataStore.getRoles()[0].name : '';
      const selectedRole = loginPrefill.role || defaultRole;
      res.render('login', {
        roles: this.dataStore.getRoles(),
        flash: res.locals.flash,
        loginPrefill,
        selectedRole
      });
    });

    this.app.post('/login', (req, res) => {
      const { username, password, role } = req.body;
      const user = this.dataStore.authenticateUser(username, password, role);
      if (!user) {
        this.setFlash(req, 'error', 'Sign-in failed: please check your username and password.');
        req.session.loginPrefill = { username, role };
        return res.redirect('/login');
      }

      if (user.status === 'suspended') {
        this.setFlash(req, 'error', 'This account is suspended and cannot sign in.');
        req.session.loginPrefill = { username, role };
        return res.redirect('/login');
      }

      req.session.user = {
        username: user.username,
        displayName: user.displayName,
        role: user.role
      };

      this.dataStore.updateUserAccount(user.username, {
        lastLogin: new Date().toISOString().replace('T', ' ').slice(0, 16)
      });

      this.setFlash(req, 'success', `${user.displayName}, welcome back!`);
      res.redirect('/dashboard');
    });

    this.app.post('/logout', this.requireAuth, (req, res) => {
      req.session.destroy(() => {
        res.redirect('/login');
      });
    });

    this.app.post('/admin/accounts/create', this.requireAdmin, (req, res) => {
      const { username, displayName, role, status, password } = req.body;
      if (!username || !displayName || !role || !status || !password) {
        this.setFlash(req, 'error', 'Please provide all required account details.');
        return res.redirect('/dashboard');
      }

      const exists = Boolean(this.dataStore.getUserAccount(username));
      if (exists) {
        this.setFlash(req, 'error', 'That username already exists. Choose a different value.');
        return res.redirect('/dashboard');
      }

      this.dataStore.addUserAccount({
        username,
        displayName,
        role,
        status,
        lastLogin: '-',
        password
      });

      this.setFlash(req, 'success', 'User account created successfully.');
      res.redirect('/dashboard');
    });

    this.app.post('/admin/accounts/update', this.requireAdmin, (req, res) => {
      const { username, displayName, role, status } = req.body;
      const updated = this.dataStore.updateUserAccount(username, { displayName, role, status });
      if (!updated) {
        this.setFlash(req, 'error', 'The specified user account could not be found.');
        return res.redirect('/dashboard');
      }
      this.setFlash(req, 'success', 'Account details updated.');
      res.redirect('/dashboard');
    });

    this.app.post('/admin/accounts/suspend', this.requireAdmin, (req, res) => {
      const { username } = req.body;
      const updated = this.dataStore.updateUserAccount(username, { status: 'suspended' });
      if (!updated) {
        this.setFlash(req, 'error', 'The specified user account could not be found.');
        return res.redirect('/dashboard');
      }
      this.setFlash(req, 'success', 'User account suspended.');
      res.redirect('/dashboard');
    });

    this.app.post('/admin/profiles/create', this.requireAdmin, (req, res) => {
      const { name, description, permissions } = req.body;
      if (!name || !description) {
        this.setFlash(req, 'error', 'Profile name and description are required.');
        return res.redirect('/dashboard');
      }

      this.dataStore.addUserProfile({
        name,
        description,
        permissions: permissions
          ? permissions.split(',').map((item) => item.trim()).filter(Boolean)
          : [],
        status: 'active'
      });

      this.setFlash(req, 'success', 'User profile created successfully.');
      res.redirect('/dashboard');
    });

    this.app.post('/admin/profiles/update', this.requireAdmin, (req, res) => {
      const { name, description, status } = req.body;
      const permissions = req.body.permissions
        ? req.body.permissions.split(',').map((item) => item.trim()).filter(Boolean)
        : [];

      const updated = this.dataStore.updateUserProfile(name, {
        description,
        status,
        permissions
      });

      if (!updated) {
        this.setFlash(req, 'error', 'The specified user profile could not be found.');
        return res.redirect('/dashboard');
      }

      this.setFlash(req, 'success', 'User profile updated.');
      res.redirect('/dashboard');
    });

    this.app.post('/admin/profiles/suspend', this.requireAdmin, (req, res) => {
      const { name } = req.body;
      const updated = this.dataStore.updateUserProfile(name, { status: 'suspended' });
      if (!updated) {
        this.setFlash(req, 'error', 'The specified user profile could not be found.');
        return res.redirect('/dashboard');
      }

      this.setFlash(req, 'success', 'User profile suspended.');
      res.redirect('/dashboard');
    });

    // Expose basic JSON endpoints so future integrations can reuse the data.
    this.app.get('/api/pin-requests', (req, res) => {
      res.json(this.dataStore.getPinRequests());
    });

    this.app.get('/api/csr-history', (req, res) => {
      res.json(this.dataStore.getCsrHistory());
    });

    this.app.get('/api/pin-matches', (req, res) => {
      res.json(this.dataStore.getPinMatches());
    });

    this.app.get('/api/service-categories', (req, res) => {
      res.json(this.dataStore.getServiceCategories());
    });

    this.app.get('/api/reports', (req, res) => {
      res.json(this.dataStore.getReports());
    });
  }

  start() {
    this.app.listen(this.port, () => {
      // Log a startup message to confirm the server is running.
      console.log(`CSR volunteer matching app listening on http://localhost:${this.port}`);
    });
  }
}

const appInstance = new VolunteerPlatformApp(dataStore);

if (require.main === module) {
  appInstance.start();
}

module.exports = appInstance;
