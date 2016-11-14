angular.module 'gps.common.models'
.config (configProvider) ->
  configProvider.set 'ranksUsCoastGuard', {
    enlisted: [
        key: 1
        level: 1
        label: "Seaman Recruit (SR)"
      ,
        key: 2
        level: 2
        label: "Seaman Apprentice (SA)"
      ,
        key: 3
        level: 3
        label: "Seaman (SN)"
      ,
        key: 4
        level: 4
        label: "Petty Officer Third Class (PO3)"
      ,
        key: 5
        level: 5
        label: "Petty Officer Second Class (PO2)"
      ,
        key: 6
        level: 6
        label: "Petty Officer First Class (PO1)"
      ,
        key: 7
        level: 7
        label: "Chief Petty Officer (CPO)"
      ,
        key: 8
        level: 8
        label: "Senior Chief Petty Officer (SCPO)"
      ,
        key: 9
        level: 9
        label: "Master Chief Petty Officer (MCPO)"
      ,
        key: 10
        level: 9
        label: "Command Master Chief (CMC)"
      ,
        key: 11
        level: 10
        label: "Master Chief Petty Officer of the Coast Guard (MCPO-CG)"
    ],
    officer: [
        key: 1
        level: 1
        label: "Ensign (ENS)"
      ,
        key: 2
        level: 2
        label: "Lieutenant Junior Grade (LTJG)"
      ,
        key: 3
        level: 3
        label: "Lieutenant (LT)"
      ,
        key: 4
        level: 4
        label: "Lieutenant Commander (LCDR)"
      ,
        key: 5
        level: 5
        label: "Commander (CDR)"
      ,
        key: 6
        level: 6
        label: "Captain (CAPT)"
      ,
        key: 7
        level: 7
        label: "Rear Admiral Lower Half (RADM)(L)"
      ,
        key: 8
        level: 8
        label: "Rear Admiral Upper Half (RADM)(U)"
      ,
        key: 9
        level: 9
        label: "Vice Admiral (VADM)"
      ,
        key: 10
        level: 10
        label: "Admiral (ADM)"
    ]
  }