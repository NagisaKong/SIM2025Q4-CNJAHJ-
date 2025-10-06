// 演示数据：集中维护页面所需的静态数据，后续可替换为真实数据库查询结果。
const requestStatuses = {
  pending: { label: "待匹配", className: "status-pending" },
  matched: { label: "已匹配", className: "status-matched" },
  completed: { label: "已完成", className: "status-completed" },
  active: { label: "启用", className: "status-active" },
  suspended: { label: "停用", className: "status-suspended" }
};

const roles = [
  {
    name: "用户管理员",
    highlights: [
      "创建、更新与停用用户账户及档案",
      "检索用户资料与权限配置",
      "监控系统访问并支持账号安全"
    ]
  },
  {
    name: "CSR 代表",
    highlights: [
      "浏览、筛选并收藏志愿机会",
      "查看志愿请求详情与历史记录",
      "完成匹配后回报服务成果"
    ]
  },
  {
    name: "求助者（PIN）",
    highlights: [
      "提交、更新与删除求助请求",
      "查看请求曝光度与兴趣度指标",
      "跟踪历史匹配记录"
    ]
  },
  {
    name: "平台管理",
    highlights: [
      "维护服务类别目录与可用性",
      "生成日报、周报与月报",
      "洞察平台运营成效"
    ]
  }
];

const pinRequests = [
  {
    title: "医院复诊陪同",
    category: "医疗陪同",
    status: "pending",
    views: 42,
    favorites: 6,
    submittedAt: "2025-02-12"
  },
  {
    title: "轮椅维护支持",
    category: "辅助出行",
    status: "matched",
    views: 38,
    favorites: 9,
    submittedAt: "2025-02-10"
  },
  {
    title: "家庭餐食协助",
    category: "日常照护",
    status: "completed",
    views: 55,
    favorites: 14,
    submittedAt: "2025-01-30"
  }
];

const csrShortlist = [
  {
    title: "社区复康运动协助",
    desc: "服务日期：2025-03-03 ｜ 类别：健康陪伴"
  },
  {
    title: "老人陪诊服务",
    desc: "服务日期：2025-02-28 ｜ 类别：医疗陪同"
  }
];

const csrHistory = [
  {
    title: "长者健康随访",
    category: "健康陪伴",
    serviceDate: "2025-01-18",
    duration: "4 小时"
  },
  {
    title: "社区图书整理",
    category: "社区建设",
    serviceDate: "2024-12-05",
    duration: "3 小时"
  },
  {
    title: "青少年学习辅导",
    category: "教育辅导",
    serviceDate: "2024-11-22",
    duration: "2 小时"
  }
];

const pinMetrics = [
  {
    title: "今日浏览总数",
    value: "128",
    description: "相较昨日提升 12%"
  },
  {
    title: "今日收藏总数",
    value: "37",
    description: "新增 9 个 CSR 代表加入候选"
  },
  {
    title: "匹配成功率",
    value: "82%",
    description: "本月成功完成 45 次匹配"
  }
];

const pinMatches = [
  {
    service: "术后复诊陪护",
    csr: "李婷",
    completedAt: "2025-01-12",
    feedback: "五星好评，感谢守时与关怀"
  },
  {
    service: "无障碍出行支持",
    csr: "王强",
    completedAt: "2024-12-20",
    feedback: "服务周到，顺利完成医院检查"
  },
  {
    service: "家庭饮食指导",
    csr: "陈珂",
    completedAt: "2024-11-30",
    feedback: "改善饮食结构，家属反馈积极"
  }
];

const serviceCategories = [
  { name: "医疗陪同", status: "active" },
  { name: "心理支持", status: "active" },
  { name: "应急支援", status: "suspended" }
];

const userAccounts = [
  {
    username: "admin.liang",
    displayName: "梁敏",
    role: "用户管理员",
    status: "active",
    lastLogin: "2025-02-14 09:30",
    password: "admin123"
  },
  {
    username: "csr.wangqi",
    displayName: "王琪",
    role: "CSR 代表",
    status: "active",
    lastLogin: "2025-02-13 18:05",
    password: "csr12345"
  },
  {
    username: "pin.lijuan",
    displayName: "李娟",
    role: "求助者（PIN）",
    status: "suspended",
    lastLogin: "2024-12-22 14:12",
    password: "pin12345"
  }
];

const userProfiles = [
  {
    name: "用户管理员",
    description: "负责系统内所有用户的账户与档案安全。",
    permissions: ["创建/更新账户", "分配角色权限", "暂停或恢复访问"],
    status: "active"
  },
  {
    name: "CSR 代表",
    description: "对接求助者需求并安排企业志愿者服务。",
    permissions: ["浏览与搜索请求", "维护候选清单", "登记服务结果"],
    status: "active"
  },
  {
    name: "求助者（PIN）",
    description: "提交与跟踪求助请求，查看曝光指标。",
    permissions: ["创建/更新求助", "查看曝光数据", "管理历史匹配"],
    status: "active"
  },
  {
    name: "平台管理",
    description: "维护服务目录并分析系统运营表现。",
    permissions: ["维护服务类别", "生成运营报表", "管理平台公告"],
    status: "active"
  }
];

const volunteerOpportunities = [
  {
    title: "医院陪诊志愿者",
    category: "医疗陪同",
    location: "静安区",
    schedule: "2025-02-18",
    status: "open"
  },
  {
    title: "轮椅出行协助",
    category: "辅助出行",
    location: "浦东新区",
    schedule: "2025-02-20",
    status: "open"
  },
  {
    title: "康复运动指导",
    category: "健康陪伴",
    location: "徐汇区",
    schedule: "2025-03-02",
    status: "filled"
  }
];

const reports = [
  {
    title: "日报",
    highlights: ["当日新发求助请求：18 条", "当日完成匹配：12 次"]
  },
  {
    title: "周报",
    highlights: ["本周参与 CSR 代表：54 位", "本周完成服务：68 次"]
  },
  {
    title: "月报",
    highlights: ["本月新增 PIN：35 位", "本月服务满意度：4.8 / 5"]
  }
];

// 数据操作函数：提供简单的内存增删改查以支持演示功能。
function authenticateUser(username, password) {
  return userAccounts.find(
    (account) => account.username === username && account.password === password
  );
}

function addUserAccount(account) {
  userAccounts.push(account);
  return account;
}

function updateUserAccount(username, updates) {
  const target = userAccounts.find((account) => account.username === username);
  if (!target) return null;
  Object.assign(target, updates);
  return target;
}

function searchUserAccounts(keyword) {
  if (!keyword) return userAccounts;
  const lower = keyword.toLowerCase();
  return userAccounts.filter(
    (account) =>
      account.username.toLowerCase().includes(lower) ||
      account.displayName.toLowerCase().includes(lower) ||
      account.role.toLowerCase().includes(lower)
  );
}

function getUserAccount(username) {
  return userAccounts.find((account) => account.username === username);
}

function addUserProfile(profile) {
  userProfiles.push(profile);
  return profile;
}

function updateUserProfile(name, updates) {
  const target = userProfiles.find((profile) => profile.name === name);
  if (!target) return null;
  Object.assign(target, updates);
  return target;
}

function searchUserProfiles(keyword) {
  if (!keyword) return userProfiles;
  const lower = keyword.toLowerCase();
  return userProfiles.filter(
    (profile) =>
      profile.name.toLowerCase().includes(lower) ||
      profile.description.toLowerCase().includes(lower)
  );
}

module.exports = {
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
  volunteerOpportunities,
  authenticateUser,
  addUserAccount,
  updateUserAccount,
  getUserAccount,
  searchUserAccounts,
  addUserProfile,
  updateUserProfile,
  searchUserProfiles
};
