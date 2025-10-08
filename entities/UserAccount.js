const BaseEntity = require('./BaseEntity');

class UserAccount extends BaseEntity {
  suspend() {
    this.status = 'suspended';
    return this;
  }

  activate() {
    this.status = 'active';
    return this;
  }

  matchesKeyword(keyword) {
    if (!keyword) return true;
    const lower = keyword.toLowerCase();
    return [this.username, this.displayName, this.role]
      .filter(Boolean)
      .some((value) => value.toLowerCase().includes(lower));
  }

  toJSON() {
    const { password, ...rest } = this;
    return { ...rest };
  }
}

module.exports = UserAccount;
