const BaseEntity = require('./BaseEntity');

class UserStory extends BaseEntity {
  constructor(props = {}) {
    const { role, action, benefit } = props;
    super({ role, action, benefit });
  }

  formatStatement() {
    const { role, action, benefit } = this;
    return `As a ${role}, I want to ${action}, so ${benefit}.`;
  }

  toJSON() {
    return {
      role: this.role,
      action: this.action,
      benefit: this.benefit,
      statement: this.formatStatement()
    };
  }
}

module.exports = UserStory;
