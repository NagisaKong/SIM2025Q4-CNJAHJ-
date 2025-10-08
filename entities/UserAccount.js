const BaseEntity = require('./BaseEntity');

class UserAccount extends BaseEntity {
  #password;

  constructor(props = {}) {
    const { password, ...rest } = props;
    super(rest);
    this.#password = password || '';
  }

  get password() {
    return undefined;
  }

  set password(value) {
    this.#password = value || '';
  }

  verifyPassword(candidate) {
    return this.#password === candidate;
  }

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

  update(fields = {}) {
    const { password, ...rest } = fields;
    if (password !== undefined) {
      this.#password = password;
    }
    return super.update(rest);
  }

  toJSON() {
    return super.toJSON();
  }
}

module.exports = UserAccount;
