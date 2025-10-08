class AdminController {
  constructor({ dataStore, flash }) {
    this.dataStore = dataStore;
    this.flash = flash;

    this.createAccount = this.createAccount.bind(this);
    this.updateAccount = this.updateAccount.bind(this);
    this.suspendAccount = this.suspendAccount.bind(this);
    this.createProfile = this.createProfile.bind(this);
    this.updateProfile = this.updateProfile.bind(this);
    this.suspendProfile = this.suspendProfile.bind(this);
  }

  createAccount(req, res) {
    const { username, displayName, role, status, password } = req.body;
    if (!username || !displayName || !role || !status || !password) {
      this.flash.setFlash(req, 'error', 'Please provide all required account details.');
      return res.redirect('/dashboard');
    }

    const exists = Boolean(this.dataStore.getUserAccount(username));
    if (exists) {
      this.flash.setFlash(req, 'error', 'That username already exists. Choose a different value.');
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

    this.flash.setFlash(req, 'success', 'User account created successfully.');
    return res.redirect('/dashboard');
  }

  updateAccount(req, res) {
    const { username, displayName, role, status } = req.body;
    const updated = this.dataStore.updateUserAccount(username, { displayName, role, status });
    if (!updated) {
      this.flash.setFlash(req, 'error', 'The specified user account could not be found.');
      return res.redirect('/dashboard');
    }
    this.flash.setFlash(req, 'success', 'Account details updated.');
    return res.redirect('/dashboard');
  }

  suspendAccount(req, res) {
    const { username } = req.body;
    const updated = this.dataStore.updateUserAccount(username, { status: 'suspended' });
    if (!updated) {
      this.flash.setFlash(req, 'error', 'The specified user account could not be found.');
      return res.redirect('/dashboard');
    }
    this.flash.setFlash(req, 'success', 'User account suspended.');
    return res.redirect('/dashboard');
  }

  createProfile(req, res) {
    const { name, description, permissions } = req.body;
    if (!name || !description) {
      this.flash.setFlash(req, 'error', 'Profile name and description are required.');
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

    this.flash.setFlash(req, 'success', 'User profile created successfully.');
    return res.redirect('/dashboard');
  }

  updateProfile(req, res) {
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
      this.flash.setFlash(req, 'error', 'The specified user profile could not be found.');
      return res.redirect('/dashboard');
    }

    this.flash.setFlash(req, 'success', 'User profile updated.');
    return res.redirect('/dashboard');
  }

  suspendProfile(req, res) {
    const { name } = req.body;
    const updated = this.dataStore.updateUserProfile(name, { status: 'suspended' });
    if (!updated) {
      this.flash.setFlash(req, 'error', 'The specified user profile could not be found.');
      return res.redirect('/dashboard');
    }

    this.flash.setFlash(req, 'success', 'User profile suspended.');
    return res.redirect('/dashboard');
  }
}

module.exports = AdminController;
