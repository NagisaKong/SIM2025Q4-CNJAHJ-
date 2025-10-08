class BaseEntity {
  constructor(props = {}) {
    Object.assign(this, props);
  }

  update(fields = {}) {
    Object.assign(this, fields);
    return this;
  }

  toJSON() {
    return { ...this };
  }
}

module.exports = BaseEntity;
