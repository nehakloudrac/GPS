angular.module 'gps.common.models'
.config (configProvider) ->
  configProvider.set 'ranksUsAirForce', {
    enlisted: [
        key: 1
        level: 1
        label: "Airman Basic (AB)"
      ,
        key: 2
        level: 2
        label: "Airman (Amn)"
      ,
        key: 3
        level: 3
        label: "Airman First Class (A1C)"
      ,
        key: 4
        level: 4
        label: "Senior Airman (SrA)"
      ,
        key: 5
        level: 5
        label: "Staff Sergeant (SSgt)"
      ,
        key: 6
        level: 6
        label: "Technical Sergeant (TSgt)"
      ,
        key: 7
        level: 7
        label: "Master Sergeant (MSgt)"
      ,
        key: 8
        level: 7
        label: "First Sergeant (E-7)"
      ,
        key: 9
        level: 8
        label: "Senior Master Sergeant (SMSgt)"
      ,
        key: 10
        level: 8
        label: "First Sergeant (E-8)"
      ,
        key: 11
        level: 9
        label: "Chief Master Sergeant (CMSgt)"
      ,
        key: 12
        level: 9
        label: "First Sergeant (E-9)"
      ,
        key: 13
        level: 9
        label: "Commander Chief Master Sergeant (CCM)"
      ,
        key: 14
        level: 10
        label: "Chief Master Sergeant of the Air Force (CMSAF)"
    ],
    officer: [
        key: 1
        level: 1
        label: "Second Lieutenant (2nd Lt)"
      ,
        key: 2
        level: 2
        label: "First Lieutenant (1st Lt)"
      ,
        key: 3
        level: 3
        label: "Captain (Capt)"
      ,
        key: 4
        level: 4
        label: "Major (Maj)"
      ,
        key: 5
        level: 5
        label: "Lieutenant Colonel (Lt Col)"
      ,
        key: 6
        level: 6
        label: "Colonel (Col)"
      ,
        key: 7
        level: 7
        label: "Brigadier General (Brig Gen)"
      ,
        key: 8
        level: 8
        label: "Major General (Maj Gen)"
      ,
        key: 9
        level: 9
        label: "Lieutenant General (Lt Gen)"
      ,
        key: 10
        level: 10
        label: "General (Gen)"
    ]
  }