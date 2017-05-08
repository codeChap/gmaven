<?php

return [
	'id',
  //'_updated',
  //'afs.acquisitionDate',
  //'afs.gla',
  //'afs.glaExpansion',
  //'afs.grade',
  //'afs.greenEnergyRating',
  //'afs.grossMonthlyRental',
  //'afs.grossMonthlyRentalSqm',
  //'afs.parkingRatio',
  //'afs.redevelopmentCompletionDate',
  //'afs.redevelopmentCost',
  //'afs.tenancy',
  //'afs.totalVacantArea',
  //'afs.vacancyPercentSqm',
  //'afs.value',
  //'afs.valueSqm',
  //'afs.yield',
  //'ani._id',
  //'ani.date',
  //'ani.selected',
  //'ani.value',
  'basic.city',
  'basic.commNodeGeoArea',
  'basic.customReferenceId',
  'basic.displayAddress',
  'basic.forSale',
  'basic.gla',
  'basic.marketingBlurb',
  'basic.name',
  'basic.ownerName',
  'basic.ownerType',
  'basic.primaryCategory',
  'basic.propertyFundDomainKey',
  'basic.propertyGrade',
  'basic.propertyGradeDate',
  'basic.province',
  'basic.provinceGeoArea',
  'basic.strategicAction',
  'basic.strategicImportance',
  'basic.subCategory',
  'basic.suburb',
  'basic.value',
  'basic.valueM2',
  'buildings._id',
  //'buildings.components',
  //'buildings.components._id',
  'businesses._id',
  'businesses.isLegalOwner',
  'businesses.isManager',
  'businesses.isOwner',
  'businesses.name',
  'businesses.type',
  'equity.debtBalance',
  'equity.debtBalanceDate',
  'equity.debtBalanceLTV',
  'equity.debtFacility',
  'equity.debtFacilityLTV',
  'equity.debtFactilityDate',
  'equity.equityVsDebtBalance',
  'equity.equityVsDebtFacility',
  'equity.paidForDate',
  'equity.paidForValue',
  'equity.valueMovement',
  'equity.valueYoYMovement',
  'geo.lat',
  'geo.lon',
  'images._id',
  'images.contentDomainKey',
  'images.contentType',
  'images.length',
  'images.name',
  //'industrial.aircon',
  //'industrial.aircon.refrigerantGas',
  //'industrial.aircon.type',
  //'industrial.amenities',
  //'industrial.amenities._key',
  //'industrial.amenities.exists',
  //'industrial.backupWaterSupply',
  //'industrial.backupWaterSupplyOther',
  //'industrial.cityImprovementDistrict',
  //'industrial.columnSpan',
  //'industrial.driveInDoors',
  //'industrial.energyEfficiency',
  //'industrial.floorCapacity',
  //'industrial.floorQuality',
  //'industrial.freightElevator',
  //'industrial.gantryCrane',
  //'industrial.gantryCrane._id',
  //'industrial.gantryCrane.capacity',
  //'industrial.gantryCrane.description',
  //'industrial.generators',
  //'industrial.generators._id',
  //'industrial.generators.covered',
  //'industrial.generators.make',
  //'industrial.generators.output',
  //'industrial.greenBuildingCertification',
  //'industrial.greenBuildingCertification.certificationYear',
  //'industrial.greenBuildingCertification.expiryDate',
  //'industrial.greenBuildingCertification.starRating',
  //'industrial.hasGantryCranes',
  //'industrial.hasGenerators',
  //'industrial.hasGreenBuildingCertification',
  //'industrial.hasRollerShutterDoors',
  //'industrial.heightToEaves',
  //'industrial.loadingDock',
  //'industrial.lux',
  //'industrial.lux.description',
  //'industrial.lux.level',
  //'industrial.powerPhase',
  //'industrial.powerSupply',
  //'industrial.powerSupply.unit',
  //'industrial.powerSupply.value',
  //'industrial.propertyAccessPrimary',
  //'industrial.propertyAccessSpecific',
  //'industrial.rollerShutterDoors',
  //'industrial.rollerShutterDoors._id',
  //'industrial.rollerShutterDoors.description',
  //'industrial.rollerShutterDoors.height',
  //'industrial.rollerShutterDoors.width',
  //'industrial.security',
  //'industrial.securityInfrastructure',
  //'industrial.sprinkler',
  //'industrial.warehouseLayout',
  //'office.aircon',
  //'office.aircon.refrigerantGas',
  //'office.aircon.type',
  //'office.amenities',
  'office.amenities._key',
  'office.amenities.exists',
  //'office.backupWaterSupply',
  //'office.backupWaterSupplyOther',
  //'office.buildingShape',
  //'office.cityImprovementDistrict',
  //'office.energyEfficiency',
  //'office.fiberProviders',
  //'office.floorPlate',
  //'office.floorPlate.maxSize',
  //'office.floorPlate.maxWidth',
  //'office.floorPlate.minSize',
  //'office.floorPlate.minWidth',
  //'office.floorPlate.multiple',
  //'office.floorPlate.size',
  //'office.floorPlate.width',
  //'office.generators',
  //'office.generators._id',
  //'office.generators.covered',
  //'office.generators.make',
  //'office.generators.output',
  //'office.greenBuildingCertification',
  //'office.greenBuildingCertification.certificationYear',
  //'office.greenBuildingCertification.expiryDate',
  //'office.greenBuildingCertification.starRating',
  //'office.hasGenerators',
  //'office.hasGreenBuildingCertification',
  //'office.hasOneFloorplateSize',
  //'office.liftCoreCount',
  //'office.liftCount',
  //'office.parkingRatio',
  //'office.powerPhase',
  //'office.powerSupply',
  //'office.powerSupply.unit',
  //'office.powerSupply.value',
  //'office.securityGuards',
  //'office.securityGuards.hours',
  //'office.securityGuards.number',
  //'office.securityInfrastructure',
  //'office.storeyCount',
  //'owners._id',
  //'owners.name',
  //'owners.typePrimary',
  //'owners.typeSecondary',
  //'ownership.deeds',
  //'ownership.deeds._id',
  //'ownership.deeds.diagramDeed',
  //'ownership.deeds.extent',
  //'ownership.deeds.majorRegion',
  //'ownership.deeds.minorRegion',
  //'ownership.deeds.owners',
  //'ownership.deeds.owners._id',
  //'ownership.deeds.owners.auditor',
  //'ownership.deeds.owners.directors',
  //'ownership.deeds.owners.directors._id',
  //'ownership.deeds.owners.directors.address',
  //'ownership.deeds.owners.directors.appointed',
  //'ownership.deeds.owners.directors.companies',
  //'ownership.deeds.owners.directors.companies._id',
  //'ownership.deeds.owners.directors.companies.name',
  //'ownership.deeds.owners.directors.idNumber',
  //'ownership.deeds.owners.directors.name',
  //'ownership.deeds.owners.name',
  //'ownership.deeds.owners.regNo',
  //'ownership.deeds.parcel',
  //'ownership.deeds.portion',
  //'ownership.deeds.remainingExtent',
  //'ownership.deeds.scheme',
  //'ownership.deeds.scheme.name',
  //'ownership.deeds.scheme.number',
  //'ownership.deeds.scheme.unitNumber',
  //'ownership.deeds.scheme.year',
  //'ownership.deeds.situated',
  //'ownership.deeds.type',
  //'ownership.funding',
  //'ownership.funding.list',
  //'ownership.funding.list._id',
  //'ownership.funding.list.amount',
  //'ownership.funding.list.entity',
  //'ownership.funding.list.type',
  //'ownership.funding.list.year',
  //'ownership.funding.umbrellas',
  //'ownership.funding.umbrellas._id',
  //'ownership.leasehold',
  //'ownership.leasehold.erfSize',
  //'ownership.leasehold.opOwners',
  //'ownership.leasehold.opOwners._id',
  //'ownership.leasehold.opOwners.date',
  //'ownership.leasehold.opOwners.logoId',
  //'ownership.leasehold.opOwners.name',
  //'ownership.leasehold.opOwners.paid',
  //'ownership.opOwners',
  //'ownership.opOwners._id',
  //'ownership.opOwners.date',
  //'ownership.opOwners.logoId',
  //'ownership.opOwners.name',
  //'ownership.opOwners.paid',
  'ownership.type',
  'sales.askingPrice',
  'sales.purchasePrice',
  'sales.valueM2',
  'sales.yield',
  'vacancy.calculatedParkingRatio',
  //'vacancy.categories',
  //'vacancy.categories._key',
  //'vacancy.categories.askingRentPerSqm',
  //'vacancy.categories.gla',
  //'vacancy.categories.msAskingRentPerSqm',
  //'vacancy.categories.msGla',
  //'vacancy.categories.primaryCategory',
  //'vacancy.categories.subCategory',
  //'vacancy.categories.totalGla',
  //'vacancy.contacts',
  //'vacancy.contacts._id',
  //'vacancy.contacts.holdsKeys',
  //'vacancy.contacts.role',
  'vacancy.currentVacantArea',
  //'vacancy.expenses',
  //'vacancy.expenses.cid',
  //'vacancy.expenses.cid.cost',
  //'vacancy.expenses.cid.has',
  //'vacancy.expenses.cleaningAndHygiene',
  //'vacancy.expenses.cleaningAndHygiene.cost',
  //'vacancy.expenses.cleaningAndHygiene.has',
  //'vacancy.expenses.generator',
  //'vacancy.expenses.generator.cost',
  //'vacancy.expenses.generator.has',
  //'vacancy.expenses.generator.includesUps',
  //'vacancy.expenses.insurance',
  //'vacancy.expenses.insurance.cost',
  //'vacancy.expenses.insurance.has',
  //'vacancy.expenses.leaseType',
  //'vacancy.expenses.maintenanceAircon',
  //'vacancy.expenses.maintenanceAircon.cost',
  //'vacancy.expenses.maintenanceAircon.has',
  //'vacancy.expenses.maintenanceExternal',
  //'vacancy.expenses.maintenanceExternal.cost',
  //'vacancy.expenses.maintenanceExternal.has',
  //'vacancy.expenses.maintenanceInternal',
  //'vacancy.expenses.maintenanceInternal.cost',
  //'vacancy.expenses.maintenanceInternal.has',
  //'vacancy.expenses.maintenanceRoof',
  //'vacancy.expenses.maintenanceRoof.cost',
  //'vacancy.expenses.maintenanceRoof.has',
  //'vacancy.expenses.operational',
  //'vacancy.expenses.operational.cost',
  //'vacancy.expenses.operational.has',
  //'vacancy.expenses.park',
  //'vacancy.expenses.park.cost',
  //'vacancy.expenses.park.has',
  //'vacancy.expenses.pestControl',
  //'vacancy.expenses.pestControl.cost',
  //'vacancy.expenses.pestControl.has',
  //'vacancy.expenses.ratesAndTaxes',
  //'vacancy.expenses.ratesAndTaxes.cost',
  //'vacancy.expenses.ratesAndTaxes.has',
  //'vacancy.expenses.ratesAndTaxes.payIncrease',
  //'vacancy.expenses.refuse',
  //'vacancy.expenses.refuse.cost',
  //'vacancy.expenses.refuse.has',
  //'vacancy.expenses.security',
  //'vacancy.expenses.security.cost',
  //'vacancy.expenses.security.has',
  //'vacancy.expenses.utilities',
  //'vacancy.expenses.utilities.combined',
  //'vacancy.expenses.utilities.combined.cost',
  //'vacancy.expenses.utilities.combined.has',
  //'vacancy.expenses.utilities.lights',
  //'vacancy.expenses.utilities.lights.cost',
  //'vacancy.expenses.utilities.lights.has',
  //'vacancy.expenses.utilities.separateCosts',
  //'vacancy.expenses.utilities.water',
  //'vacancy.expenses.utilities.water.cost',
  //'vacancy.expenses.utilities.water.has',
  //'vacancy.leasing',
  //'vacancy.leasing.annualEscalation',
  //'vacancy.leasing.askingPeriod',
  //'vacancy.leasing.commissionMultiplier',
  //'vacancy.leasing.depositFormula',
  //'vacancy.leasing.depositRequired',
  //'vacancy.leasing.guaranteeFormula',
  //'vacancy.leasing.guaranteeRequired',
  //'vacancy.leasing.namingRightsProperty',
  //'vacancy.leasing.specialDeals',
  //'vacancy.leasing.suretyFormula',
  //'vacancy.leasing.suretyRequired',

  //'vacancy.parkingBays',
  //'vacancy.parkingBays._key',
  //'vacancy.parkingBays.msNumber',
  //'vacancy.parkingBays.msValue',
  //'vacancy.parkingBays.number',
  //'vacancy.parkingBays.vacantNumber',
  //'vacancy.parkingBays.value',

  'vacancy.signoffDate',
  'vacancy.totalUnitArea',
  'vacancy.unitSignoffDate',
  'vacancy.useVacantParkingRatio',
  'vacancy.vacantParkingRatio',
  'vacancy.weightedAskingRental',
  'valuations._id',
  'valuations.date',
  'valuations.isActive',
  'valuations.source',
  'valuations.value',
  'yard.access',
  'yard.askingRental',
  //'yard.breakdown',
  //'yard.breakdown._id',
  //'yard.breakdown.description',
  //'yard.breakdown.extent',
  //'yard.breakdown.netRent',
  'yard.description',
  'yard.divisible',
  'yard.extent',
  'yard.has',
  'yard.leasedSeparately',
  'yard.other',
  'yard.type'
];