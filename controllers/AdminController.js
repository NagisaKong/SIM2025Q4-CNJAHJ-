class AdminController {
  #dataStore;
  #flash;

  constructor({ dataStore, flash }) {
    this.#dataStore = dataStore;
    this.#flash = flash;

    this.createAccount = this.createAccount.bind(this);
    this.updateAccount = this.updateAccount.bind(this);
    this.suspendAccount = this.suspendAccount.bind(this);
    this.createProfile = this.createProfile.bind(this);
    this.updateProfile = this.updateProfile.bind(this);
    this.suspendProfile = this.suspendProfile.bind(this);
  }

  #setErrorAndRedirect(req, res, message) {
    this.#flash.setFlash(req, 'error', message);
    return res.redirect('/dashboard');
  }

  #parsePermissions(raw) {
    return raw ? raw.split(',').map((item) => item.trim()).filter(Boolean) : [];
  }

  createAccount(req, res) {
    const { username, displayName, role, status, password } = req.body;
    if (!username || !displayName || !role || !status || !password) {
      return this.#setErrorAndRedirect(req, res, 'Please provide all required account details.');
    }

    const exists = Boolean(this.#dataStore.getUserAccount(username));
    if (exists) {
      return this.#setErrorAndRedirect(
        req,
        res,
        'That username already exists. Choose a different value.'
      );
    }

    this.#dataStore.addUserAccount({
      username,
      displayName,
      role,
      status,
      lastLogin: '-',
      password
    });

    this.#flash.setFlash(req, 'success', 'User account created successfully.');
    return res.redirect('/dashboard');
  }

  updateAccount(req, res) {
    const { username, displayName, role, status } = req.body;
    const updated = this.#dataStore.updateUserAccount(username, { displayName, role, status });
    if (!updated) {
      return this.#setErrorAndRedirect(
        req,
        res,
        'The specified user account could not be found.'
      );
    }
    this.#flash.setFlash(req, 'success', 'Account details updated.');
    return res.redirect('/dashboard');
  }

  suspendAccount(req, res) {
    const { username } = req.body;
    const updated = this.#dataStore.updateUserAccount(username, { status: 'suspended' });
    if (!updated) {
      return this.#setErrorAndRedirect(
        req,
        res,
        'The specified user account could not be found.'
      );
    }
    this.#flash.setFlash(req, 'success', 'User account suspended.');
    return res.redirect('/dashboard');
  }

  createProfile(req, res) {
    const { name, description, permissions } = req.body;
    if (!name || !description) {
      return this.#setErrorAndRedirect(req, res, 'Profile name and description are required.');
    }

    this.#dataStore.addUserProfile({
      name,
      description,
      permissions: this.#parsePermissions(permissions),
      status: 'active'
    });

    this.#flash.setFlash(req, 'success', 'User profile created successfully.');
    return res.redirect('/dashboard');
  }

  updateProfile(req, res) {
    const { name, description, status } = req.body;
    const permissions = this.#parsePermissions(req.body.permissions);

    const updated = this.#dataStore.updateUserProfile(name, {
      description,
      status,
      permissions
    });

    if (!updated) {
      return this.#setErrorAndRedirect(
        req,
        res,
        'The specified user profile could not be found.'
      );
    }

    this.#flash.setFlash(req, 'success', 'User profile updated.');
    return res.redirect('/dashboard');
  }

  suspendProfile(req, res) {
    const { name } = req.body;
    const updated = this.#dataStore.updateUserProfile(name, { status: 'suspended' });
    if (!updated) {
      return this.#setErrorAndRedirect(
        req,
        res,
        'The specified user profile could not be found.'
      );
    }

    this.#flash.setFlash(req, 'success', 'User profile suspended.');
    return res.redirect('/dashboard');
  }
}

module.exports = AdminController;
