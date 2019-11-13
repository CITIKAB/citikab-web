<title>My trip</title>


<?php $__env->startSection('main'); ?>


    <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 flexbox__item page-content" style="padding:0px;" ng-controller="trip">
    <input type="hidden" name="user_id" value="<?php echo e(@Auth::user()->id); ?>" id="user_id">
    <div class="hidden--palm">
        <div class="page-lead text-center">
            <div class="flexbox">
                <div class="flexbox__item col-lg-4 col-md-4 col-sm-4 col-xs-12 text--left"> 
                <a data-toggle="collapse" id="show-filter" href="#trip-filterer" class="trip-filter__origin-link collapsed hide-sm-760"><span class="icon icon_settings-alt color--dark soft-half--right"></span><?php echo e(trans('messages.profile.filter')); ?></a>
                </div>
                <div class="flexbox__item col-lg-4 col-md-4 col-sm-4 col-xs-12"><h1 class="flush flush-h1"><?php echo e(trans('messages.header.mytrips')); ?></h1></div>
                <div class="flexbox__item col-lg-4 col-md-4 col-sm-4 col-xs-12 text--right"></div>
            </div>
        </div>
        <div class="separated--bottom soft--ends text--center hide-md-760">
            <div data-toggle="collapse" data-target="#trip-filterer" class="btn btn--primary btn--large trip-filter__origin-btn collapsed"><span class="icon icon_settings-alt alpha"></span>
            </div>
        </div>
    </div>
    <div id="trip-filters" class="trip-filters">
        <div id="trip-filters-active" class="trip-filters__active"></div>
        <form id="trip-filterer" data-replace="data-replace" data-button-loader="#trip-filterer-loader" data-button-loader-parent="#trip-filterer-button" class="trip-filters__form trip-filter collapse" style="height: auto;">
        <div class="trip-filter__item" ng-if="!selected_filter" style="overflow:hidden;">
            <div class="grid">
                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 palm-one-whole trip-filter__header" style="padding:0px 30px;">
                <h3 class="gamma" style="margin:0px;"><?php echo e(trans('messages.profile.timeframe')); ?></h3>
                </div>
                <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 palm-one-whole black-form trip-form">
                <input type="radio" id="month-<?php echo e(date('Y-m', strtotime('0 month'))); ?>-radio" name="month" value="<?php echo e(date('Y-m', strtotime('0 month'))); ?>" class="hidden">
                <label style="cursor: pointer;" for="month-<?php echo e(date('Y-m', strtotime('0 month'))); ?>-radio"  value="<?php echo e(date('Y-m', strtotime('0 month'))); ?>" class="btn-input btn--uniform push--right month-filter" month="<?php echo e(date('F', strtotime('0 month'))); ?>" ><?php echo e(date('F', strtotime('0 month'))); ?>

                </label>

                <input type="radio" id="month-<?php echo e(date('Y-m', strtotime('first day of -1 month'))); ?>-radio" name="month" value="<?php echo e(date('Y-m', strtotime('first day of -1 month'))); ?>" class="hidden">                                
                <label style="cursor: pointer;" for="month-<?php echo e(date('Y-m', strtotime('first day of -1 month'))); ?>-radio" value="<?php echo e(date('Y-m', strtotime('first day of -1 month'))); ?>" class="btn-input btn--uniform push--right month-filter"  month="<?php echo e(date('F', strtotime('first day of -1 month'))); ?>"><?php echo e(date('F', strtotime('first day of -1 month'))); ?></label>

                <input type="radio" id="month-<?php echo e(date('Y-m', strtotime('first day of -2 month'))); ?>-radio" name="month" value="<?php echo e(date('Y-m', strtotime('first day of -2 month'))); ?>" class="hidden">
                <label style="cursor: pointer;" for="month-<?php echo e(date('Y-m', strtotime('first day of -2 month'))); ?>-radio" value="<?php echo e(date('Y-m', strtotime('first day of -2 month'))); ?>" class="btn-input btn--uniform push--right month-filter" month="<?php echo e(date('F', strtotime('first day of -2 month'))); ?>"><?php echo e(date('F', strtotime('first day of -2 month'))); ?></label>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 trip-filter__item">
                    <button ng-click="getTrips()" id="trip-filterer-button" type="submit" class="btn btn--primary btn--full btn--large btn-blue"><span class="btn-loader collapse" id="trip-filterer-loader"><span class="icon icon_spinner alpha"></span></span><span><?php echo e(trans('messages.profile.filter')); ?></span></button>
                </div>
            </div>
        </div>
        <div class="trip-filter__item tripvalue" ng-if="selected_filter">
            <div class="grid grid--full filtertrip">
                <!-- <div class="grid__item one-twelfth trip-filter__header gridfull">
                        <a href="<?php echo e(url('trip')); ?>" data-replace="data-replace" class="fa fa-close close_style closebtn"></a>
                </div> -->
                <div class="grid__item eleven-twelfths cf filtercheck">
                    <div data-button-loader="#month-filter-loader" data-button-loader-parent="false" class="btn-group btn-group--bordered btn-group--joined inline-group push--right float--left btninput ">
                        <div class="inline-group__item inlinebtn" style="width: 100px;">
                            <a href="<?php echo e(url('trip')); ?>" data-replace="data-replace" class="btn btn--primary month-cls custom-cls customvalue" style="    background-color: #1ca7c0 !important;border-color: #1ca7c0 !important;height: 28px;"><span class="btn-loader collapse" id="month-filter-loader">
                                <span class="icon icon_spinner"></span></span>
                                <div id="selected_month" class="btn-input btn--uniform push--right month-filter filter-checked"></div>
                            </a>
                        </div>
                        <a href="<?php echo e(url('trip')); ?>" data-replace="data-replace" class="btn btn--primary close_btn" style="    background-color: #1ca7c0 !important;border-color: #1ca7c0 !important;height: 28px;">
                            <span class="icon icon_delete micro smallbtn"></span></a>
                        
                    </div>
                </div>
            </div>
        </div>
    </form>
    </div>
    <div id="no-more-tables" class="more-table-trips">
     <table class="col-sm-12 table-bordered table-striped table-condensed cf">
        <thead class="cf">
            <tr>
                <th></th>
                <th class="width-60"><?php echo e(trans('messages.profile.pickup')); ?></th>
                <th class="hide-sm"><?php echo e(trans('messages.profile.driver')); ?></th>
                <th class="width-20"><?php echo e(trans('messages.profile.fare')); ?></th>
                <th class="width-20"><?php echo e(trans('messages.profile.car')); ?></th>
                <th class="hide-sm"><?php echo e(trans('messages.profile.location')); ?></th>
                <th class="hide-sm">Payment Method</th>
            </tr>
        </thead>
            <tbody class="all-trips-table" ng-init="trips=<?php echo e($trips); ?>;currentPage=trips.current_page;totalPages=trips.last_page;" ng-repeat="trip in trips">
                
                <tr class="trip-expand__origin collapsed" data-toggle="collapse" data-target="#trip-{{ trip.id}}" ng-cloak>
                    <td class="text--center"><span class="icon icon_right-arrow micro trip-expand__arrow"></span></td>
                    <td data-title="Pickup">{{ trip.begin_date|date:'MM/dd/yyyy'}}</td>
                    <td class="hide-sm" data-title="Driver">{{ trip.driver_name}}</td>
                    <td data-title="Fare"><span ng-bind-html="trip.currency.original_symbol"></span>&nbsp;{{ trip.total_fare}}
                        <br><span class="text-danger">{{ trip.status }}</span></td>
                    <td data-title="Car">{{ trip.vehicle_name}}</td>
                    <td data-title="City" class="hide-sm">{{ trip.pickup_location }}</td>

                    <td class="hide-sm" data-title="Payment Method">
                        <span class="soft-half--sides">{{ trip.payment_mode }}</span>
                    </td>
                    <!-- <td class="text--center"><span class="icon icon_right-arrow micro trip-expand__arrow"></span></td>
                    <td data-title="Pickup">02/25/17</td>
                    <td class="hide-sm" data-title="Driver">Sivaranjani Rajesh</td>
                    <td data-title="Fare">$168.43</td>
                    <td data-title="Car">GoferX</td>
                    <td data-title="City" class="hide-sm">Chennai</td>
                    <td class="hide-sm" data-title="Payment Method">
                        <span class="sprite_payment-type-default_icon"></span>
                        <span class="soft-half--sides">•••• </span>
                    </td> -->
                </tr>    
                     
                <tr class="hard">
                    <td colspan="8">
                        <div id="trip-{{ trip.id }}" class="collapse" style="height: auto;">
                        <div class="trip-expand trip-expand--completed">
                            <div id="trip-{{ trip.id }}-expand" class="flexbox">
        <div class="flexbox__item col-lg-4 col-md-4 col-sm-12 hidden--portable hide-sm-760" style="height: 200px">
            <div id="trip-map-{{ trip.id }}"></div>
            <div class="fixed-ratio fixed-ratio--1-1" style="height: 100%;">                
                <img ng-if="trip.status!='Completed' || trip.status!='Rating'" src="http://maps.googleapis.com/maps/api/staticmap?size=640x480&zoom=14&path=color:0x000000ff%7Cweight:4%7Cenc:{{ trip.trip_path }}&markers=size:mid|icon:<?php echo e(url('images/pickup.png')); ?>|{{ trip.pickup_latitude}},{{ trip.pickup_longitude}}&markers=size:mid|icon:<?php echo e(url('images/drop.png')); ?>|{{ trip.drop_latitude}},{{ trip.drop_longitude}}&sensor=false&key=<?php echo e($map_key); ?>" alt="Map image" class="hide-sm-760img--full img--flush img--bordered fixed-ratio__content sr_radio"  style="object-fit: cover;" >   

                 <img src="{{ trip.map_image}}" ng-if="trip.status=='Completed' || trip.status=='Rating'" " alt="Map image" class="hide-sm-760img--full img--flush img--bordered fixed-ratio__content sr_radio"  style="object-fit: cover;" >  
            </div>           
        </div>
        <div class="flexbox__item col-lg-4 col-md-4 col-sm-6 palm-one-whole lap-one-half soft--sides">
            <h3 class="alpha push-half--bottom" style="margin: 0px 0px 10px 0px !important; font-weight: 600;"><!-- ₹ -->
                <span ng-bind-html="trip.currency.original_symbol"></span>&nbsp;{{ trip.total_fare }}</h3>
                <p class="soft--bottom flush" style="width: 80px;
    margin: 0px auto !important;">
            <span class="soft-half--sides">{{ trip.payment_mode }}</span>
            </p>
            <!-- <p class="soft--bottom flush" style="width: 80px;
    margin: 0px auto !important;">
            <span class="icon sprite_payment-type-default_icon" ></span>
            <span class="soft-half--sides">•••• </span>
            </p> -->
            <h6 class="soft-half--ends separated--bottom color--neutral" style="padding:10px 0px;">{{ trip.pickup_date_time }}</h6>
            <div class="text--left">
                <div class="trip-address grid grid--full soft-double--bottom">
                    <div class="trip-address__path"></div>
                    <div class="grid__item one-tenth" style="margin:6px 0px;">
                        <div class="icon icon_route-dot color--positive"></div>
                    </div>
                    <div class="grid__item nine-tenths">
                        <p class="flush">{{ trip.pickup_time }}</p>
                        <h6 class="color--neutral flush">{{ trip.pickup_location }}</h6>
                    </div>
                </div>
                <div class="trip-address grid grid--full">
                    <div class="grid__item one-tenth" style="margin:6px 0px;">
                        <div class="icon icon_route-dot color--negative"></div>
                    </div>
                    <div class="grid__item nine-tenths">
                        <p class="flush">{{ trip.drop_time }}</p>
                        <h6 class="color--neutral flush">{{ trip.drop_location }}</h6>
                    </div>
                </div>
            </div>
        </div>
<div class="flexbox__item col-lg-4 col-md-4 col-sm-6 lap-one-half separated--left soft-double--left hidden--palm">

<div class="trip-info-tools"><hr class="push--bottom">
<ul class="nav nav--stacked soft-half--top  center-block col-lg-9 col-md-9 col-sm-9 col-xs-12"><li class="push-half--bottom"><form method="post" action="<?php echo e(url('trips/resend_receipt')); ?>" data-replace="data-replace" data-replace-pushstate="false"><input type="hidden" name="x-csrf-token" value="eBYrQqLCtPkRHpYKWiJe9ZqMCag6UtCe">
<!-- <div class="btn-group btn-group--bordered btn-group--joined inline-group"><button style="    padding: 0px 37px 0px 0px !important;margin-left: 16px;
    font-size: 14px !important;" type="submit" class="btn btn--primary btn-blue"><span style="padding: 7px;" class="icon icon_receipt"></span>Resend</button>
</div> -->
</form></li><li>
<a href="<?php echo e(url('trip_detail')); ?>/{{ trip.id}}" class="btn-group btn-group--bordered btn-group--joined inline-group"><div class="inline-group__item">
    <button  style="    padding: 0px 15px 0px 0px !important;
    font-size: 14px !important;" type="submit" class="btn btn--primary btn-blue"><span style="padding: 7px;" class="icon icon_search"></span><?php echo e(trans('messages.profile.view_detail')); ?></button>
</div>

</div></a></li></ul></div></div></div></div></div></td></tr>
                    </tbody>
                    <tbody class="all-trips-table">
                    <tr >
                        <td ng-show="trips.length==0" colspan="6" style="height: 46px;text-align: center;">
                        <?php echo e(trans('messages.dashboard.no_details')); ?>

                        </td>
                    </tr>
                    </tbody>
                    
                    </table>
    </div>    
    <div style="padding:25px;">
        <div class="pagination-buttons-container row-space-8 float--right" ng-cloak>
            <div class="results_count pagination inline-group btn-group btn-group--bordered" style="float: right;margin-top: 20px;">
                <div class="inline-group__item" ng-show="trips.length > 1">
                    <posts-pagination></posts-pagination>
                </div>
            </div>   
        </div>
    </div>
    </div>
    </div>
</div>
</div>
</div>
</main>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('template_dashboard', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>