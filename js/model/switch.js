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

  setLast_power_on(json_last_power_on) {
    this.set((self) => {
      self.last_power_on = this.jsonToDate(json_last_power_on);
    });
  }

  setPower_off(json_power_off) {
    this.set((self) => {
      self.power_off = this.jsonToDate(json_power_off);
      // Damos por hecho que el tiempo entre que se crea el objeto y el momento en el que se usa es suficientemente pequeño
      self.isOn = this.power_off>=new Date();
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

  jsonToDate(json_date) {
    // Extraer la fecha del objeto (se supone horario UTC)
    return new Date(json_date.date);
  }
}
