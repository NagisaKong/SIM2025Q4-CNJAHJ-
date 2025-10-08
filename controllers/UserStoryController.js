class UserStoryController {
  #dataStore;
  #flash;

  constructor({ dataStore, flash }) {
    this.#dataStore = dataStore;
    this.#flash = flash;

    this.showUserStories = this.showUserStories.bind(this);
  }

  showUserStories(req, res) {
    res.render('userStories', {
      currentUser: req.session.user,
      storiesByRole: this.#dataStore.getUserStories(),
      flash: this.#flash.consumeFlash(res)
    });
  }
}

module.exports = UserStoryController;
