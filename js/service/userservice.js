class UserService {
  constructor() {

  }

  login(userData) {
    return new Promise((resolve, reject) => {

      $.ajax({
        url: AppConfig.backendServer + '/rest/account',
        method: 'POST',
        data: JSON.stringify(userData),
        contentType: 'application/json'
      })
        .then((response) => {
          const {user_name, jwt_token} = response;

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
            beforeSend: (xhr) => {
            }
          });
          reject(error);
        });
    });
  }

  loginWithSessionData() {
    return new Promise((resolve, reject) => {
      const userName = window.sessionStorage.getItem('user_name');
      const jwtToken = window.sessionStorage.getItem('jwt_token');

      if (userName != null && jwtToken != null) {
        this.checkIfIsValidToken({user_name: userName, jwt_token: jwtToken})
          .then(() => resolve({logged: userName})) // Devuelve un objeto con el valor userName
          .catch(reject);
      } else {
        resolve(null);
      }
    });
  }

  checkIfIsValidToken(userData) {
    return new Promise((resolve, reject) => {

      $.ajax({
        url: AppConfig.backendServer + '/rest/account/checkToken',
        method: 'PUT',
        data: JSON.stringify(userData),
        contentType: 'application/json'
      })
        .then((response) => {
          const {user_name, jwt_token} = response;

          window.sessionStorage.setItem('user_name', user_name);
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
            beforeSend: (xhr) => {
            }
          });
          reject(error);
        });
    });
  }

  logout() {
    window.sessionStorage.removeItem('user_name');
    window.sessionStorage.removeItem('jwt_token');
    $.ajaxSetup({
      beforeSend: (xhr) => {
      }
    });
  }

  register(user) {
    return $.ajax({
      url: AppConfig.backendServer + '/rest/account/new',
      method: 'POST',
      data: JSON.stringify(user),
      contentType: 'application/json'
    });
  }
}
