class SwitchModel extends Fronty.Model {

  constructor(public_uuid, private_uuid, owner_name, switch_name, switch_description, last_power_on, power_off) {
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

    if (last_power_on) {
      this.last_power_on = last_power_on;
    }

    if (power_off) {
      this.power_off = power_off;
    }

    if (switch_description) {
      this.description = switch_description;
    }
  }

  setPrivate_uuid(privateUuid) {
    this.set((self) => {
      self.privateUuid = privateUuid;
    });
  }

  setOwner(owner) {
    this.set((self) => {
      self.owner = owner;
    });
  }

  setPublic_uuid(public_uuid) {
    this.set((self) => {
      self.public_uuid = public_uuid;
    });
  }

  setSwitch_name(switch_name) {
    this.set((self) => {
      self.switch_name = switch_name;
    });
  }

  setLast_power_on(last_power_on) {
    this.set((self) => {
      self.last_power_on = last_power_on;
    });
  }

  setPower_off(power_off) {
    this.set((self) => {
      self.power_off = power_off;
    });
  }

  setDescription(description) {
    this.set((self) => {
      self.description = description;
    });
  }


}
