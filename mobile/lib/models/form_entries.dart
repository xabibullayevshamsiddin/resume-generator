/// Bitta mehnat faoliyati qatori (backend'dagi `employment[i]` bilan mos).
class EmploymentEntry {
  EmploymentEntry({
    this.period = '',
    this.organization = '',
    this.position = '',
  });

  String period;
  String organization;
  String position;
}

/// Bitta qarindosh qatori (backend'dagi `relatives[i]` bilan mos).
class RelativeEntry {
  RelativeEntry({
    this.relationship = 'Otasi',
    this.relationshipOther = '',
    this.fullName = '',
    this.birthYear = '',
    this.birthPlace = '',
    this.workplace = '',
    this.position = '',
    this.address = '',
    this.phone = '',
  });

  String relationship;
  String relationshipOther;
  String fullName;
  String birthYear;
  String birthPlace;
  String workplace;
  String position;
  String address;
  String phone;

  bool get isOtherRelationship => relationship == 'Boshqa';
}
