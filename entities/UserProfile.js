const BaseEntity = require('./BaseEntity');

class UserProfile extends BaseEntity {
  matchesKeyword(keyword) {
    if (!keyword) return true;
    const lower = keyword.toLowerCase();
    return [this.name, this.description]
      .filter(Boolean)
      .some((value) => value.toLowerCase().includes(lower));
  }
}

module.exports = UserProfile;
