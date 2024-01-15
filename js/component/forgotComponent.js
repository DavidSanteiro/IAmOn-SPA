class ForgotComponent extends Fronty.ModelComponent {
  constructor(userModel, router) {
    super(Handlebars.templates.forgot, userModel);
    this.userModel = userModel;
    this.userService = new UserService();
    this.router = router;

    this.userModel.finishRecoveryProtocol();

    this.addEventListener('click', '#id_submit_1', () => {
      let user_email = $('#id_userEmailForgot').val();
      this.userService.askForSecurityCode({
        user_email: ((user_email.trim()==='')?null:user_email),
      })
        .then((data) => {
          alert(data);

          this.userModel.startRecoveryProtocol();

          this.addEventListener('click', '#id_submit_2', () => {
            let user_email = $('#id_userEmailForgot').val();
            let security_code = $('#id_userSecurityCode').val();
            let user_password = $('#id_userPasswordForgot').val();
            this.userService.resetPassword({
              user_email: ((user_email.trim()==='')?null:user_email),
              security_code: ((security_code.trim()==='')?null:security_code),
              user_password: ((user_password.trim()==='')?null:user_password)
            })
              .then((data) => {
                // alert(I18n.translate('string'));
                alert(data);
                this.userModel.finishRecoveryProtocol();
                this.router.goToPage('login');
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
