class LoginComponent extends Fronty.ModelComponent {
  constructor(userModel, router) {
    super(Handlebars.templates.login, userModel);
    this.userModel = userModel;
    this.userService = new UserService();
    this.router = router;

    this.addEventListener('click', '#id_submitButtonLogin', () => {
      this.userService.login({
        user_name: $('#id_userNameLogin').val(),
        user_password: $('#id_userPasswordLogin').val()
      })
        .then(() => {
          this.router.goToPage('dashboard');
          this.userModel.setLoggeduser($('#id_userNameLogin').val());
        })
        .catch((error) => {
          this.userModel.set((model) => {
            model.loginError = error.responseText;
          });
          this.userModel.logout();
        });
    });

  }
}
