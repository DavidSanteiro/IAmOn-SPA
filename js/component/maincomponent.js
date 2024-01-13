class MainComponent extends Fronty.RouterComponent {
  constructor() {
    super('frontyapp', Handlebars.templates.loggedOut, 'maincontent');

    // models instantiation
    // we can instantiate models at any place
    this.userModel = new UserModel();
    this.switchesModel = new SwitchesModel();
    this.userService = new UserService();

    super.setRouterConfig({
      dashboard: {
        component: new DashboardComponent(this.switchesModel, this.userModel, this),
        title: 'Dashboard'
      },
      'view-switch': {
        component: new SwitchViewComponent(this.switchesModel, this.userModel, this),
        title: 'View Switch'
      },
      'edit-switch': {
        component: new SwitchEditComponent(this.switchesModel, this.userModel, this),
        title: 'Edit Post'
      },
      'add-switch': {
        component: new SwitchAddComponent(this.switchesModel, this.userModel, this),
        title: 'Add Post'
      },
      login: {
        component: new LoginComponent(this.userModel, this),
        title: 'Login'
      },
      register: {
        component: new RegisterComponent(this.userModel, this),
        title: 'Register'
      },
      defaultRoute: 'dashboard'
    });

    Handlebars.registerHelper('currentPage', () => {
          return super.getCurrentPage();
    });

    // this.addChildComponent(this._createUserBarComponent());
    this.addChildComponent(this._createHeadersComponent());
    this.addChildComponent(this._createLanguageComponent());

  }

  start() {
    super.start(); // now we can call start
  }

  // _createUserBarComponent() {
  //   var userbar = new Fronty.ModelComponent(Handlebars.templates.user, this.userModel, 'userbar');
  //
  //   userbar.addEventListener('click', '#logoutbutton', () => {
  //     this.userModel.logout();
  //     this.userService.logout();
  //   });
  //
  //   return userbar;
  // }

  _createHeadersComponent() {
    var headersComponent = new Fronty.ModelComponent(Handlebars.templates.headers, this.userModel, 'headers');

    headersComponent.addEventListener('click', '#logout_header', () => {
      this.userModel.logout();
      this.userService.logout();
      super.goToPage('login');
    });

    headersComponent.addEventListener('click', '#logout_nav', () => {
      this.userModel.logout();
      this.userService.logout();
      super.goToPage('login');
    });

    return headersComponent;
  }

  _createLanguageComponent() {
    var languageComponent = new Fronty.ModelComponent(Handlebars.templates.language, this.routerModel, 'languagecontrol');
    // language change links
    languageComponent.addEventListener('click', '#englishlink', () => {
      I18n.changeLanguage('default');
      document.location.reload();
    });

    languageComponent.addEventListener('click', '#spanishlink', () => {
      I18n.changeLanguage('es');
      document.location.reload();
    });

    return languageComponent;
  }
}
