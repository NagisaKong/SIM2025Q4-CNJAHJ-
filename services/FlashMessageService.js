class FlashMessageService {
  setFlash(req, type, message) {
    req.session.flash = { type, message };
  }

  storeLoginPrefill(req, data) {
    req.session.loginPrefill = data;
  }

  consumeFlash(res) {
    return res.locals.flash || null;
  }

  consumeLoginPrefill(res) {
    return res.locals.loginPrefill || {};
  }
}

module.exports = FlashMessageService;
