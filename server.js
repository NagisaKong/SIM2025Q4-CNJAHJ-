// 应用入口：搭建 Express 服务以提供服务端渲染页面与演示 API。
const express = require('express');
const path = require('path');
const morgan = require('morgan');
const session = require('express-session');

const {
  requestStatuses,
  roles,
  pinRequests,
  csrShortlist,
  csrHistory,
  pinMetrics,
  pinMatches,
  serviceCategories,
  reports,
  volunteerOpportunities,
  authenticateUser,
  addUserAccount,
  updateUserAccount,
  getUserAccount,
  searchUserAccounts,
  addUserProfile,
  updateUserProfile,
  searchUserProfiles
} = require('./data/sampleData');

const app = express();
const PORT = process.env.PORT || 3000;

// 配置视图引擎为 EJS，并指定视图文件所在目录。
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// 使用 Morgan 记录请求日志，便于调试与后期运维。
app.use(morgan('dev'));

// 配置静态资源目录，使浏览器能够访问样式、图片等文件。
app.use(express.static(path.join(__dirname, 'public')));
app.use(express.urlencoded({ extended: true }));

app.use(
  session({
    secret: 'csr-volunteer-secret',
    resave: false,
    saveUninitialized: false
  })
);

// 中间件：统一注入 flash 信息与登录表单回填数据，避免模板读取未定义变量。
app.use((req, res, next) => {
  res.locals.flash = req.session.flash || null;
  res.locals.loginPrefill = req.session.loginPrefill || null;
  delete req.session.flash;
  delete req.session.loginPrefill;
  next();
});

function setFlash(req, type, message) {
  req.session.flash = { type, message };
}

function requireAuth(req, res, next) {
  if (!req.session.user) {
    setFlash(req, 'error', '请先登录以访问平台主页。');
    return res.redirect('/login');
  }
  next();
}

function requireAdmin(req, res, next) {
  if (!req.session.user || req.session.user.role !== '用户管理员') {
    setFlash(req, 'error', '仅用户管理员可以执行此操作。');
    return res.redirect('/dashboard');
  }
  next();
}

app.get('/', (req, res) => {
  if (req.session.user) {
    return res.redirect('/dashboard');
  }
  res.redirect('/login');
});

// 平台主页：需登录后访问，根据查询条件输出筛选结果。
app.get('/dashboard', requireAuth, (req, res) => {
  const accountKeyword = req.query.accountSearch || '';
  const profileKeyword = req.query.profileSearch || '';
  const flash = res.locals.flash || null;

  res.render('index', {
    roles,
    pinRequests,
    csrShortlist,
    csrHistory,
    pinMetrics,
    pinMatches,
    serviceCategories,
    requestStatuses,
    reports,
    accounts: searchUserAccounts(accountKeyword),
    profiles: searchUserProfiles(profileKeyword),
    volunteerOpportunities,
    accountKeyword,
    profileKeyword,
    currentUser: req.session.user,
    flash
  });
});

// 登录页面：提供多角色登录界面示例，演示角色切换与辅助说明。
app.get('/login', (req, res) => {
  if (req.session.user) {
    return res.redirect('/dashboard');
  }
  const loginPrefill = res.locals.loginPrefill || {};
  const defaultRole = roles.length ? roles[0].name : '';
  const selectedRole = loginPrefill.role || defaultRole;
  res.render('login', { roles, flash: res.locals.flash, loginPrefill, selectedRole });
});

app.post('/login', (req, res) => {
  const { username, password, role } = req.body;
  const user = authenticateUser(username, password, role);
  if (!user) {
    setFlash(req, 'error', '登录失败：请确认用户名与密码。');
    req.session.loginPrefill = { username, role };
    return res.redirect('/login');
  }

  if (user.status === 'suspended') {
    setFlash(req, 'error', '该账户已被暂停，无法登录。');
    req.session.loginPrefill = { username, role };
    return res.redirect('/login');
  }

  req.session.user = {
    username: user.username,
    displayName: user.displayName,
    role: user.role
  };

  updateUserAccount(user.username, {
    lastLogin: new Date().toISOString().replace('T', ' ').slice(0, 16)
  });

  setFlash(req, 'success', `${user.displayName}，欢迎回来！`);
  res.redirect('/dashboard');
});

app.post('/logout', requireAuth, (req, res) => {
  req.session.destroy(() => {
    res.redirect('/login');
  });
});

app.post('/admin/accounts/create', requireAdmin, (req, res) => {
  const { username, displayName, role, status, password } = req.body;
  if (!username || !displayName || !role || !status || !password) {
    setFlash(req, 'error', '请完整填写账户信息。');
    return res.redirect('/dashboard');
  }

  const exists = Boolean(getUserAccount(username));
  if (exists) {
    setFlash(req, 'error', '该用户名已存在，无法重复创建。');
    return res.redirect('/dashboard');
  }

  addUserAccount({
    username,
    displayName,
    role,
    status,
    lastLogin: '-',
    password
  });

  setFlash(req, 'success', '用户账户创建完成。');
  res.redirect('/dashboard');
});

app.post('/admin/accounts/update', requireAdmin, (req, res) => {
  const { username, displayName, role, status } = req.body;
  const updated = updateUserAccount(username, { displayName, role, status });
  if (!updated) {
    setFlash(req, 'error', '未找到对应的用户账户。');
    return res.redirect('/dashboard');
  }
  setFlash(req, 'success', '账户信息已更新。');
  res.redirect('/dashboard');
});

app.post('/admin/accounts/suspend', requireAdmin, (req, res) => {
  const { username } = req.body;
  const updated = updateUserAccount(username, { status: 'suspended' });
  if (!updated) {
    setFlash(req, 'error', '未找到对应的用户账户。');
    return res.redirect('/dashboard');
  }
  setFlash(req, 'success', '用户账户已暂停。');
  res.redirect('/dashboard');
});

app.post('/admin/profiles/create', requireAdmin, (req, res) => {
  const { name, description, permissions } = req.body;
  if (!name || !description) {
    setFlash(req, 'error', '档案名称与描述为必填项。');
    return res.redirect('/dashboard');
  }

  addUserProfile({
    name,
    description,
    permissions: permissions
      ? permissions.split(',').map((item) => item.trim()).filter(Boolean)
      : [],
    status: 'active'
  });

  setFlash(req, 'success', '用户档案创建完成。');
  res.redirect('/dashboard');
});

app.post('/admin/profiles/update', requireAdmin, (req, res) => {
  const { name, description, status } = req.body;
  const permissions = req.body.permissions
    ? req.body.permissions.split(',').map((item) => item.trim()).filter(Boolean)
    : [];

  const updated = updateUserProfile(name, {
    description,
    status,
    permissions
  });

  if (!updated) {
    setFlash(req, 'error', '未找到对应的用户档案。');
    return res.redirect('/dashboard');
  }

  setFlash(req, 'success', '用户档案信息已更新。');
  res.redirect('/dashboard');
});

app.post('/admin/profiles/suspend', requireAdmin, (req, res) => {
  const { name } = req.body;
  const updated = updateUserProfile(name, { status: 'suspended' });
  if (!updated) {
    setFlash(req, 'error', '未找到对应的用户档案。');
    return res.redirect('/dashboard');
  }

  setFlash(req, 'success', '用户档案已暂停使用。');
  res.redirect('/dashboard');
});

// 提供基础的 JSON API，便于未来接入真实前端或进行接口测试。
app.get('/api/pin-requests', (req, res) => {
  res.json(pinRequests);
});

app.get('/api/csr-history', (req, res) => {
  res.json(csrHistory);
});

app.get('/api/pin-matches', (req, res) => {
  res.json(pinMatches);
});

app.get('/api/service-categories', (req, res) => {
  res.json(serviceCategories);
});

app.get('/api/reports', (req, res) => {
  res.json(reports);
});

app.listen(PORT, () => {
  // 在控制台输出启动成功提示，方便确认服务状态。
  console.log(`CSR volunteer matching app listening on http://localhost:${PORT}`);
});
