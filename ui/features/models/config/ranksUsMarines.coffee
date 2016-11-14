angular.module 'gps.common.models'
.config (configProvider) ->
  configProvider.set 'ranksUsMarines', {
    enlisted: [
        key: 1
        level: 1
        label: "Private (Pvt)"
      ,
        key: 2
        level: 2
        label: "Private First (PFC)"
      ,
        key: 3
        level: 3
        label: "Lance Corporal (LCpl)"
      ,
        key: 4
        level: 4
        label: "Corporal (Cpl)"
      ,
        key: 5
        level: 5
        label: "Sergeant (Sgt)"
      ,
        key: 6
        level: 6
        label: "Staff Sergeant (SSgt)"
      ,
        key: 7
        level: 7
        label: "Gunnery Sergeant (GySgt)"
      ,
        key: 8
        level: 8
        label: "Master Sergeant (MSgt)"
      ,
        key: 9
        level: 8
        label: "First Sergeant (1stSgt)"
      ,
        key: 10
        level: 9
        label: "Master Gunnery Sergeant (MGySgt)"
      ,
        key: 11
        level: 9
        label: "Sergeant Major (SgtMaj)"
      ,
        key: 12
        level: 10
        label: "Sergeant Major of the Marine Corps (SgtMajMC)"
    ],
    officer: [
        key: 1
        level: 1
        label: "Second Lieutenant (2ndLt)"
      ,
        key: 2
        level: 2
        label: "First Lieutenant (1stLt)"
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
        label: "Lieutenant Colonel (LtCol)"
      ,
        key: 6
        level: 6
        label: "Colonel (Col)"
      ,
        key: 7
        level: 7
        label: "Brigadier General (BGen)"
      ,
        key: 8
        level: 8
        label: "Major General (MajGen)"
      ,
        key: 9
        level: 9
        label: "Lieutenant General (LtGen)"
      ,
        key: 10
        level: 10
        label: "General (Gen)"
    ]
  }