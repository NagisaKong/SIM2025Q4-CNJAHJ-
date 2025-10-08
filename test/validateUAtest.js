const request = require('supertest');
const appInstance = require('../server');

describe('Login page validation', () => {
  it('renders the login form with required inputs', async () => {
    const response = await request(appInstance.app).get('/login');

    expect(response.status).toBe(200);
    expect(response.text).toContain('Account Login');
    expect(response.text).toContain('name="role"');
    expect(response.text).toContain('name="username"');
    expect(response.text).toContain('name="password"');
  });

  it('prompts signed-in users to visit the dashboard', async () => {
    const agent = request.agent(appInstance.app);

    await agent
      .post('/login')
      .type('form')
      .send({
        role: 'User Administrator',
        username: 'admin.reed',
        password: 'admin123'
      });

    const response = await agent.get('/login');

    expect(response.status).toBe(302);
    expect(response.headers.location).toBe('/dashboard');
  });
});
