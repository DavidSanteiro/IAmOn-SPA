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

          // Guardar el nombre de usuario como cookie para poder acceder desde otras pestañas sin iniciar sesión
          this.setCookie('user_name', user_name, 1);
          // Guardar el token JWT como cookie para poder acceder desde otras pestañas sin iniciar sesión
          this.setCookie('jwt_token', jwt_token, 1);

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

      if (userName != null && userName !== "undefined"
        && jwtToken != null && jwtToken !== "undefined") {
        this.checkIfIsValidToken({user_name: userName, jwt_token: jwtToken})
          .then(() => {
            // Refrescamos el Session Storage
            window.sessionStorage.setItem('user_name', userName);
            window.sessionStorage.setItem('jwt_token', jwtToken);

            // Configurar el encabezado 'Authorization' para futuras solicitudes
            $.ajaxSetup({
              beforeSend: (xhr) => {
                const token = window.sessionStorage.getItem('jwt_token');
                xhr.setRequestHeader("Authorization", `Bearer ${token}`);
              }
            });
            resolve(userName);

          }) // Devuelve un objeto con el valor userName
          // .catch(reject);
          .catch(error => {
            console.error(error);
            reject(error);
          });
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
    // Eliminamos los datos de session storage
    window.sessionStorage.removeItem('user_name');
    window.sessionStorage.removeItem('jwt_token');
    // Eliminamos los datos de las cookies
    this.deleteCookie('user_name');
    this.deleteCookie('jwt_token')
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

  askForSecurityCode(user_email){
    return $.ajax({
      url: AppConfig.backendServer + '/rest/account/password',
      method: 'PUT',
      data: JSON.stringify(user_email),
      contentType: 'application/json'
    });
  }

  resetPassword(data){
    return $.ajax({
      url: AppConfig.backendServer + '/rest/account/password',
      method: 'POST',
      data: JSON.stringify(data),
      contentType: 'application/json'
    });
  }

  // Funciones para trabajar con las cookies
  getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i <ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  }

  setCookie(cname, cvalue, exdays) {
    const d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    let expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
  }

  deleteCookie(cname) {
    document.cookie = cname + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
  }
}
