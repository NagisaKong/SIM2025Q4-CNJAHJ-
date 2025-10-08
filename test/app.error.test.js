const request = require('supertest');
const appInstance = require('../server');

describe('Access control and error handling', () => {
  it('prevents non-admin users from using administrator routes', async () => {
    const agent = request.agent(appInstance.app);

    await agent
      .post('/login')
      .type('form')
      .send({
        role: 'CSR Representative',
        username: 'csr.wilson',
        password: 'csr12345'
      });

    const response = await agent
      .post('/admin/accounts/create')
      .type('form')
      .send({
        username: 'test.user',
        displayName: 'Test User',
        role: 'Person in Need (PIN)',
        status: 'active',
        password: 'secret'
      });

    expect(response.status).toBe(302);
    expect(response.headers.location).toBe('/dashboard');

    const dashboard = await agent.get('/dashboard');
    expect(dashboard.text).toContain('Only user administrators can perform this action.');
  });

  it('blocks suspended accounts from signing in', async () => {
    const agent = request.agent(appInstance.app);

    const response = await agent
      .post('/login')
      .type('form')
      .send({
        role: 'Person in Need (PIN)',
        username: 'pin.jordan',
        password: 'pin12345'
      });

    expect(response.status).toBe(302);
    expect(response.headers.location).toBe('/login');

    const loginPage = await agent.get('/login');
    expect(loginPage.text).toContain('This account is suspended and cannot sign in.');
  });

  it('redirects anonymous visitors away from the user story catalogue', async () => {
    const response = await request(appInstance.app).get('/user-stories');

    expect(response.status).toBe(302);
    expect(response.headers.location).toBe('/login');
  });
});
