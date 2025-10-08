class AuthController {
  constructor({ dataStore, flash }) {
    this.dataStore = dataStore;
    this.flash = flash;

    this.redirectRoot = this.redirectRoot.bind(this);
    this.showLogin = this.showLogin.bind(this);
    this.login = this.login.bind(this);
    this.logout = this.logout.bind(this);
    this.requireAuth = this.requireAuth.bind(this);
    this.requireAdmin = this.requireAdmin.bind(this);
  }

  redirectRoot(req, res) {
    if (req.session.user) {
      return res.redirect('/dashboard');
    }
    return res.redirect('/login');
  }

  showLogin(req, res) {
    if (req.session.user) {
      return res.redirect('/dashboard');
    }

    const roles = this.dataStore.getRoles();
    const defaultRole = roles.length ? roles[0].name : '';
    const loginPrefill = this.flash.consumeLoginPrefill(res);
    const selectedRole = loginPrefill.role || defaultRole;

    res.render('login', {
      roles,
      flash: this.flash.consumeFlash(res),
      loginPrefill,
      selectedRole
    });
  }

  login(req, res) {
    const { username, password, role } = req.body;
    const user = this.dataStore.authenticateUser(username, password, role);

    if (!user) {
      this.flash.setFlash(req, 'error', 'Sign-in failed: please check your username and password.');
      this.flash.storeLoginPrefill(req, { username, role });
      return res.redirect('/login');
    }

    if (user.status === 'suspended') {
      this.flash.setFlash(req, 'error', 'This account is suspended and cannot sign in.');
      this.flash.storeLoginPrefill(req, { username, role });
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

    this.flash.setFlash(req, 'success', `${user.displayName}, welcome back!`);
    return res.redirect('/dashboard');
  }

  logout(req, res) {
    req.session.destroy(() => {
      res.redirect('/login');
    });
  }

  requireAuth(req, res, next) {
    if (!req.session.user) {
      this.flash.setFlash(req, 'error', 'Please sign in before accessing the dashboard.');
      return res.redirect('/login');
    }
    return next();
  }

  requireAdmin(req, res, next) {
    if (!req.session.user || req.session.user.role !== 'User Administrator') {
      this.flash.setFlash(req, 'error', 'Only user administrators can perform this action.');
      return res.redirect('/dashboard');
    }
    return next();
  }
}

module.exports = AuthController;
