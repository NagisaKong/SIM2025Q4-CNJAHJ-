// 应用入口：搭建 Express 服务以提供服务端渲染页面与演示 API。
const express = require('express');
const path = require('path');
const morgan = require('morgan');

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
  userAccounts,
  userProfiles,
  volunteerOpportunities
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

// 根路由：渲染平台主页并注入所有演示数据。
app.get('/', (req, res) => {
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
    userAccounts,
    userProfiles,
    volunteerOpportunities
  });
});

// 登录页面：提供多角色登录界面示例，演示角色切换与辅助说明。
app.get('/login', (req, res) => {
  res.render('login', {
    roles,
    requestStatuses
  });
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
