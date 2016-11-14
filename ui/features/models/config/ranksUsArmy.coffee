angular.module 'gps.common.models'
.config (configProvider) ->
  configProvider.set 'ranksUsArmy', {
    enlisted: [
        key: 1
        level: 1
        label: "Private E-1 (PV1)"
      ,
        key: 2
        level: 2
        label: "Private E-2 (PV2)"
      ,
        key: 3
        level: 3
        label: "Private First Class (PFC)"
      ,
        key: 4
        level: 4
        label: "Specialist (SPC)"
      ,
        key: 5
        level: 5
        label: "Sergeant (SGT)"
      ,
        key: 6
        level: 6
        label: "Staff Sergeant (SSG)"
      ,
        key: 7
        level: 7
        label: "Sergeant First Class (SFC)"
      ,
        key: 8
        level: 8
        label: "Master Sergeant (MSG)"
      ,
        key: 9
        level: 8
        label: "First Sergeant (1SG)"
      ,
        key: 10
        level: 9
        label: "Sergeant Major (SGM)"
      ,
        key: 11
        level: 9
        label: "Command Sergeant Major (CSM)"
      ,
        key: 12
        level: 10
        label: "Sergeant Major of the Army (SMA)"
    ],
    officer: [
        key: 1
        level: 1
        label: "Second Lieutenant (2LT)"
      ,
        key: 2
        level: 2
        label: "First Lieutenant (1LT)"
      ,
        key: 3
        level: 3
        label: "Captain (CPT)"
      ,
        key: 4
        level: 4
        label: "Major (MAJ)"
      ,
        key: 5
        level: 5
        label: "Lieutenant Colonel (LTC)"
      ,
        key: 6
        level: 6
        label: "Colonel (COL)"
      ,
        key: 7
        level: 7
        label: "Brigadier General (BG)"
      ,
        key: 8
        level: 8
        label: "Major General (MG)"
      ,
        key: 9
        level: 9
        label: "Lieutenant General (LTG)"
      ,
        key: 10
        level: 10
        label: "General (GEN)"
      ,
        key: 11
        level: 11
        label: "General of the Army (GA)"
    ]
  }