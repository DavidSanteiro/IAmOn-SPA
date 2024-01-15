class SwitchModel extends Fronty.Model {

  constructor(public_uuid, private_uuid, owner_name, switch_name, switch_description, json_last_power_on, json_power_off) {
    super('SwitchModel'); //call super

    if (public_uuid) {
      this.public_uuid = public_uuid;
    }

    if (private_uuid) {
      this.private_uuid = private_uuid;
    }

    if (owner_name) {
      this.owner_name = owner_name;
    }

    if (switch_name) {
      this.switch_name = switch_name;
    }

    if (json_last_power_on) {
      this.setLast_power_on(json_last_power_on);
    }

    if (json_power_off) {
      this.setPower_off(json_power_off);
    }else{
      this.isOn = false;
    }

    if (switch_description) {
      this.switch_description = switch_description;
    }

    this.hasPermissions = undefined;
    this.is_subscribed = undefined;
    this.num_subscriptions = undefined;
  }

  setHasPermissions(hasPermissions) {
    this.set((self) => {
      self.hasPermissions = hasPermissions;
    });
  }

  setLast_power_on(timeISO8601) {
    this.set((self) => {
      self.last_power_on = new Date(timeISO8601);

      // Integrar hora en formato HH:MM
      let hours = self.last_power_on.getHours();
      let minutes = self.last_power_on.getMinutes();

      hours = (hours < 10) ? "0" + hours : hours;
      minutes = (minutes < 10) ? "0" + minutes : minutes;

      self.last_power_on.HHMM = hours + ":" + minutes; // Ejemplo: "15:03"

      // Integrar fecha y hora en formato DD/MM/YYYY
      let day = self.last_power_on.getDate();
      let month = self.last_power_on.getMonth() + 1; // getMonth() devuelve un valor de 0 a 11 -> se debe sumar 1
      let year = self.last_power_on.getFullYear();

      day = (day < 10) ? "0" + day : day;
      month = (month < 10) ? "0" + month : month;

      self.last_power_on.DDMMAAAA = day + "/" + month + "/" + year; // Ejemplo: "14/03/2022"
    });
  }

  setPower_off(timeISO8601) {
    this.set((self) => {
      self.power_off = new Date(timeISO8601);
      // Damos por hecho que el tiempo entre que se crea el objeto y el momento en el que se usa es suficientemente pequeño
      self.isOn = this.power_off >= new Date();
    });
  }

  setIsSubscribed(isSubscribed){
    this.set((self) => {
      self.is_subscribed = isSubscribed;
    });
  }

  setNumSubscribers(num_subscriptions) {
    this.set((self) => {
      self.num_subscriptions = num_subscriptions;
    });
  }
}
