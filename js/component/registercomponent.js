class RegisterComponent extends Fronty.ModelComponent {
  constructor(userModel, router) {
    super(Handlebars.templates.register, userModel);
    this.userModel = userModel;
    this.userService = new UserService();
    this.router = router;

    this.addEventListener('click', '#id_submitButtonRegister', () => {
      this.userService.register({
        user_name: $('#id_userNameRegister').val(),
        user_email: $('#id_userEmailRegister').val(),
        user_password: $('#id_userPasswordRegister').val()
      })
        .then(() => {
          alert(I18n.translate('User registered! You can now log in'));
          this.userModel.set((model) => {
            model.registerErrors = {};
            model.registerMode = false;
          });
        })
        .fail((xhr, errorThrown, statusText) => {
          if (xhr.status == 400) {
            this.userModel.set(() => {
              this.userModel.registerErrors = xhr.responseJSON;
            });
          } else {
            alert('An error has occurred during request: ' + statusText + '.' + xhr.responseText);
          }
        });
    });
  }
}
