class UserService {
  constructor() {

  }

  loginWithSessionData() {
    var self = this;
    return new Promise((resolve, reject) => {
      if (window.sessionStorage.getItem('user_name') &&
        window.sessionStorage.getItem('jwt_token')) {
        self.checkIfIsValidToken({
          user_name: window.sessionStorage.getItem('user_name'),
          user_password: window.sessionStorage.getItem('jwt_token')
        })
          .then(() => {
            resolve(window.sessionStorage.getItem('user_name'));
          })
          .catch(() => {
            reject();
          });
      } else {
        resolve(null);
      }
    });
  }

  login(userPasswd) {
    return new Promise((resolve, reject) => {

      $.ajax({
        url: AppConfig.backendServer+'/rest/account',
        method: 'POST',
        data: JSON.stringify(userPasswd),
        contentType: 'application/json'
      })
        .then((response) => {
          const { user_name, jwt_token } = response;

          // Guardar el nombre de usuario en sessionStorage
          window.sessionStorage.setItem('user_name', user_name);

          // Guardar el token JWT en sessionStorage
          window.sessionStorage.setItem('jwt_token', jwt_token);

          // Configurar el encabezado 'Authorization' para futuras solicitudes
          $.ajaxSetup({
            beforeSend: (xhr) => {
              const token = window.sessionStorage.getItem('jwt_token');
              xhr.setRequestHeader("Authorization", `Bearer ${token}`);
            }
          });
          resolve();
        })
        .fail((error) => {
          window.sessionStorage.removeItem('user_name');
          window.sessionStorage.removeItem('jwt_token');
          $.ajaxSetup({
            beforeSend: (xhr) => {}
          });
          reject(error);
        });
    });
  }

  checkIfIsValidToken(userPasswd){
    return new Promise((resolve, reject) => {

      $.ajax({
        url: AppConfig.backendServer+'/rest/account',
        method: 'PUT',
        data: JSON.stringify(userPasswd),
        contentType: 'application/json'
      })
        .then((response) => {
          const { user_name, jwt_token } = response;

          // Guardar el nombre de usuario en sessionStorage
          window.sessionStorage.setItem('user_name', user_name);

          // Guardar el token JWT en sessionStorage
          window.sessionStorage.setItem('jwt_token', jwt_token);

          // Configurar el encabezado 'Authorization' para futuras solicitudes
          $.ajaxSetup({
            beforeSend: (xhr) => {
              const token = window.sessionStorage.getItem('jwt_token');
              xhr.setRequestHeader("Authorization", `Bearer ${token}`);
            }
          });
          resolve();
        })
        .fail((error) => {
          window.sessionStorage.removeItem('user_name');
          window.sessionStorage.removeItem('jwt_token');
          $.ajaxSetup({
            beforeSend: (xhr) => {}
          });
          reject(error);
        });
    });
  }

  logout() {
    window.sessionStorage.removeItem('user_name');
    window.sessionStorage.removeItem('jwt_token');
    $.ajaxSetup({
      beforeSend: (xhr) => {}
    });
  }

  register(user) {
    return $.ajax({
      url: AppConfig.backendServer+'/rest/account/new',
      method: 'POST',
      data: JSON.stringify(user),
      contentType: 'application/json'
    });
  }
}
