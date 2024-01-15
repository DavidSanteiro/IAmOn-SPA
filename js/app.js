/* Main iamon-front script */

//load external resources
function loadTextFile(url) {
  return new Promise((resolve, reject) => {
    $.get({
      url: url,
      cache: true,
      beforeSend: function( xhr ) {
        xhr.overrideMimeType( "text/plain" );
      }
    }).then((source) => {
      resolve(source);
    }).fail(() => reject());
  });
}


// Configuration
var AppConfig = {
  backendServer: 'http://localhost'
  //backendServer: '/iamon'
}

Handlebars.templates = {};
Promise.all([
    I18n.initializeCurrentLanguage('js/i18n'),

    // Layouts
    loadTextFile('templates/layouts/layout.hbs').then((source) =>
      Handlebars.templates.layout = Handlebars.compile(source)),

    // Component supporting layout:
    loadTextFile('templates/components/headers.hbs').then((source) =>
    Handlebars.templates.headers = Handlebars.compile(source)),

    loadTextFile('templates/components/language.hbs').then((source) =>
      Handlebars.templates.language = Handlebars.compile(source)),
    loadTextFile('templates/components/user.hbs').then((source) =>
      Handlebars.templates.user = Handlebars.compile(source)),

    // User templates
    loadTextFile('templates/components/login.hbs').then((source) =>
      Handlebars.templates.login = Handlebars.compile(source)),
    loadTextFile('templates/components/forgot.hbs').then((source) =>
      Handlebars.templates.forgot = Handlebars.compile(source)),
    loadTextFile('templates/components/register.hbs').then((source) =>
      Handlebars.templates.register = Handlebars.compile(source)),

    // Swtiches templates
    loadTextFile('templates/components/dashboard.hbs').then((source) =>
    Handlebars.templates.dashboard = Handlebars.compile(source)),
  loadTextFile('templates/components/switch-add.hbs').then((source) =>
    Handlebars.templates.switchadd = Handlebars.compile(source)),

    loadTextFile('templates/components/switch-edit.hbs').then((source) =>
      Handlebars.templates.switchedit = Handlebars.compile(source)),

    loadTextFile('templates/components/switch-view.hbs').then((source) =>
      Handlebars.templates.switchview = Handlebars.compile(source)),

  ])
  .then(() => {
    $(() => {
      new MainComponent().start();
    });
  }).catch((err) => {
    alert('FATAL: could not start app ' + err);
  });
