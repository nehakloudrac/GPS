angular.module 'gps.common.candidate-profile'
.config (configProvider) ->
  configProvider.set 'paymentStatuses', [
      key: 'paid'
      label: "Paid"
    ,
      key: 'unpaid'
      label: "Unpaid"
  ]
