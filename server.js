// Application entry point: configure an Express server with server-rendered views and demo APIs.
const express = require('express');
const path = require('path');
const morgan = require('morgan');
const session = require('express-session');

const {
  requestStatuses,
  roles,
  pinRequests,
  csrShortlist,
  csrHistory,
  pinMetrics,
  pinMatches,
  serviceCategories,
  reports,
  volunteerOpportunities,
  authenticateUser,
  addUserAccount,
  updateUserAccount,
  getUserAccount,
  searchUserAccounts,
  addUserProfile,
  updateUserProfile,
  searchUserProfiles
} = require('./data/sampleData');

const app = express();
const PORT = process.env.PORT || 3000;

// Configure EJS as the templating engine and point to the views directory.
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Use Morgan to log requests for easier debugging and maintenance.
app.use(morgan('dev'));

// Serve static assets so the browser can load stylesheets and other files.
app.use(express.static(path.join(__dirname, 'public')));
app.use(express.urlencoded({ extended: true }));

app.use(
  session({
    secret: 'csr-volunteer-secret',
    resave: false,
    saveUninitialized: false
  })
);

// Middleware: expose flash messages and login prefill data to templates.
app.use((req, res, next) => {
  res.locals.flash = req.session.flash || null;
  res.locals.loginPrefill = req.session.loginPrefill || null;
  delete req.session.flash;
  delete req.session.loginPrefill;
  next();
});

function setFlash(req, type, message) {
  req.session.flash = { type, message };
}

function requireAuth(req, res, next) {
  if (!req.session.user) {
    setFlash(req, 'error', 'Please sign in before accessing the dashboard.');
    return res.redirect('/login');
  }
  next();
}

function requireAdmin(req, res, next) {
  if (!req.session.user || req.session.user.role !== 'User Administrator') {
    setFlash(req, 'error', 'Only user administrators can perform this action.');
    return res.redirect('/dashboard');
  }
  next();
}

app.get('/', (req, res) => {
  if (req.session.user) {
    return res.redirect('/dashboard');
  }
  res.redirect('/login');
});

// Dashboard: requires authentication and applies optional search filters.
app.get('/dashboard', requireAuth, (req, res) => {
  const accountKeyword = req.query.accountSearch || '';
  const profileKeyword = req.query.profileSearch || '';
  const flash = res.locals.flash || null;

  res.render('index', {
    roles,
    pinRequests,
    csrShortlist,
    csrHistory,
    pinMetrics,
    pinMatches,
    serviceCategories,
    requestStatuses,
    reports,
    accounts: searchUserAccounts(accountKeyword),
    profiles: searchUserProfiles(profileKeyword),
    volunteerOpportunities,
    accountKeyword,
    profileKeyword,
    currentUser: req.session.user,
    flash
  });
});

// Login page: shared entry point for every role with role selection.
app.get('/login', (req, res) => {
  if (req.session.user) {
    return res.redirect('/dashboard');
  }
  const loginPrefill = res.locals.loginPrefill || {};
  const defaultRole = roles.length ? roles[0].name : '';
  const selectedRole = loginPrefill.role || defaultRole;
  res.render('login', { roles, flash: res.locals.flash, loginPrefill, selectedRole });
});

app.post('/login', (req, res) => {
  const { username, password, role } = req.body;
  const user = authenticateUser(username, password, role);
  if (!user) {
    setFlash(req, 'error', 'Sign-in failed: please check your username and password.');
    req.session.loginPrefill = { username, role };
    return res.redirect('/login');
  }

  if (user.status === 'suspended') {
    setFlash(req, 'error', 'This account is suspended and cannot sign in.');
    req.session.loginPrefill = { username, role };
    return res.redirect('/login');
  }

  req.session.user = {
    username: user.username,
    displayName: user.displayName,
    role: user.role
  };

  updateUserAccount(user.username, {
    lastLogin: new Date().toISOString().replace('T', ' ').slice(0, 16)
  });

  setFlash(req, 'success', `${user.displayName}, welcome back!`);
  res.redirect('/dashboard');
});

app.post('/logout', requireAuth, (req, res) => {
  req.session.destroy(() => {
    res.redirect('/login');
  });
});

app.post('/admin/accounts/create', requireAdmin, (req, res) => {
  const { username, displayName, role, status, password } = req.body;
  if (!username || !displayName || !role || !status || !password) {
    setFlash(req, 'error', 'Please provide all required account details.');
    return res.redirect('/dashboard');
  }

  const exists = Boolean(getUserAccount(username));
  if (exists) {
    setFlash(req, 'error', 'That username already exists. Choose a different value.');
    return res.redirect('/dashboard');
  }

  addUserAccount({
    username,
    displayName,
    role,
    status,
    lastLogin: '-',
    password
  });

  setFlash(req, 'success', 'User account created successfully.');
  res.redirect('/dashboard');
});

app.post('/admin/accounts/update', requireAdmin, (req, res) => {
  const { username, displayName, role, status } = req.body;
  const updated = updateUserAccount(username, { displayName, role, status });
  if (!updated) {
    setFlash(req, 'error', 'The specified user account could not be found.');
    return res.redirect('/dashboard');
  }
  setFlash(req, 'success', 'Account details updated.');
  res.redirect('/dashboard');
});

app.post('/admin/accounts/suspend', requireAdmin, (req, res) => {
  const { username } = req.body;
  const updated = updateUserAccount(username, { status: 'suspended' });
  if (!updated) {
    setFlash(req, 'error', 'The specified user account could not be found.');
    return res.redirect('/dashboard');
  }
  setFlash(req, 'success', 'User account suspended.');
  res.redirect('/dashboard');
});

app.post('/admin/profiles/create', requireAdmin, (req, res) => {
  const { name, description, permissions } = req.body;
  if (!name || !description) {
    setFlash(req, 'error', 'Profile name and description are required.');
    return res.redirect('/dashboard');
  }

  addUserProfile({
    name,
    description,
    permissions: permissions
      ? permissions.split(',').map((item) => item.trim()).filter(Boolean)
      : [],
    status: 'active'
  });

  setFlash(req, 'success', 'User profile created successfully.');
  res.redirect('/dashboard');
});

app.post('/admin/profiles/update', requireAdmin, (req, res) => {
  const { name, description, status } = req.body;
  const permissions = req.body.permissions
    ? req.body.permissions.split(',').map((item) => item.trim()).filter(Boolean)
    : [];

  const updated = updateUserProfile(name, {
    description,
    status,
    permissions
  });

  if (!updated) {
    setFlash(req, 'error', 'The specified user profile could not be found.');
    return res.redirect('/dashboard');
  }

  setFlash(req, 'success', 'User profile updated.');
  res.redirect('/dashboard');
});

app.post('/admin/profiles/suspend', requireAdmin, (req, res) => {
  const { name } = req.body;
  const updated = updateUserProfile(name, { status: 'suspended' });
  if (!updated) {
    setFlash(req, 'error', 'The specified user profile could not be found.');
    return res.redirect('/dashboard');
  }

  setFlash(req, 'success', 'User profile suspended.');
  res.redirect('/dashboard');
});

// Expose basic JSON endpoints so future integrations can reuse the data.
app.get('/api/pin-requests', (req, res) => {
  res.json(pinRequests);
});

app.get('/api/csr-history', (req, res) => {
  res.json(csrHistory);
});

app.get('/api/pin-matches', (req, res) => {
  res.json(pinMatches);
});

app.get('/api/service-categories', (req, res) => {
  res.json(serviceCategories);
});

app.get('/api/reports', (req, res) => {
  res.json(reports);
});

app.listen(PORT, () => {
  // Log a startup message to confirm the server is running.
  console.log(`CSR volunteer matching app listening on http://localhost:${PORT}`);
});
