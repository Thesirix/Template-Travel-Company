<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('air_ports', function (Blueprint $table) {
            $table->foreign(['ville_id'], 'air_ports_ville_id_foreign')->references(['id'])->on('villes')->onDelete('cascade');
        });
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign(['gent_id'], 'attendances_gent_id_foreign')->references(['id'])->on('gents')->onDelete('cascade');
        });
        Schema::table('black_notes', function (Blueprint $table) {
            $table->foreign(['gent_id'], 'black_notes_ibfk_1')->references(['id'])->on('gents');
        });
        Schema::table('block_power_confirmations', function (Blueprint $table) {
            $table->foreign(['agent_id'], 'block_power_confirmations_ibfk_1')->references(['id'])->on('gents')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign(['passenger_id'], 'block_power_confirmations_ibfk_2')->references(['id'])->on('block_power_passengers')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('block_power_options', function (Blueprint $table) {
            $table->foreign(['agent_id'], 'block_power_options_ibfk_1')->references(['id'])->on('gents');
        });
        Schema::table('block_power_passengers', function (Blueprint $table) {
            $table->foreign(['option_id'], 'block_power_passengers_ibfk_1')->references(['id'])->on('block_power_options')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('block_power_releases', function (Blueprint $table) {
            $table->foreign(['option_id'], 'block_power_releases_ibfk_1')->references(['id'])->on('block_power_options')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign(['agent_id'], 'block_power_releases_ibfk_2')->references(['id'])->on('gents');
        });
        Schema::table('boat_arrival_ports', function (Blueprint $table) {
            $table->foreign(['destination_id'], 'boat_arrival_ports_destination_id_foreign')->references(['id'])->on('boat_destinations')->onDelete('restrict')->onUpdate('cascade');
        });
        Schema::table('boat_bookings', function (Blueprint $table) {
            $table->foreign(['accommodation_id'], 'boat_bookings_accommodation_id_foreign')->references(['id'])->on('boat_accommodations')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign(['arrival_port_id'], 'boat_bookings_arrival_port_id_foreign')->references(['id'])->on('boat_arrival_ports')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign(['company_id'], 'boat_bookings_company_id_foreign')->references(['id'])->on('boat_companies')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign(['departure_port_id'], 'boat_bookings_departure_port_id_foreign')->references(['id'])->on('boat_departure_ports')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign(['destination_id'], 'boat_bookings_destination_id_foreign')->references(['id'])->on('boat_destinations')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign(['fare_id'], 'boat_bookings_fare_id_foreign')->references(['id'])->on('boat_fares')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign(['gent_id'], 'boat_bookings_ibfk_1')->references(['id'])->on('gents')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign(['insurance_id'], 'boat_bookings_insurance_id_foreign')->references(['id'])->on('boat_passenger_insurances')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign(['vehicle_id'], 'boat_bookings_vehicle_id_foreign')->references(['id'])->on('boat_vehicles')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign(['vehicle_insurance_id'], 'boat_bookings_vehicle_insurance_id_foreign')->references(['id'])->on('boat_vehicle_insurances')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign(['vehicle_size_id'], 'boat_bookings_vehicle_size_id_foreign')->references(['id'])->on('boat_vehicle_sizes')->onDelete('restrict')->onUpdate('cascade');
        });
        Schema::table('boat_companies', function (Blueprint $table) {
            $table->foreign(['destination_id'], 'boat_companies_destination_id_foreign')->references(['id'])->on('boat_destinations')->onDelete('restrict')->onUpdate('cascade');
        });
        Schema::table('boat_departure_ports', function (Blueprint $table) {
            $table->foreign(['destination_id'], 'boat_departure_ports_destination_id_foreign')->references(['id'])->on('boat_destinations')->onDelete('restrict')->onUpdate('cascade');
        });
        Schema::table('boat_passengers', function (Blueprint $table) {
            $table->foreign(['booking_id'], 'boat_passengers_ibfk_1')->references(['id'])->on('boat_bookings')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('boat_reservations', function (Blueprint $table) {
            $table->foreign(['booking_id'], 'boat_reservations_ibfk_1')->references(['id'])->on('boat_bookings')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('calendars', function (Blueprint $table) {
            $table->foreign(['gent_id'], 'calendars_ibfk_1')->references(['id'])->on('gents')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('client_vol_estimates', function (Blueprint $table) {
            $table->foreign(['agence_id'], 'client_vol_estimates_ibfk_1')->references(['id'])->on('agences');
            $table->foreign(['gent_id'], 'client_vol_estimates_ibfk_2')->references(['id'])->on('gents');
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->foreign(['country_id'], 'companies_country_id_foreign')->references(['id'])->on('countries')->onDelete('cascade');
        });
        Schema::table('destinations', function (Blueprint $table) {
            $table->foreign(['country_id'], 'destinations_country_id_foreign')->references(['id'])->on('countries')->onDelete('set null');
        });
        Schema::table('gents', function (Blueprint $table) {
            $table->foreign(['agence_id'], 'gents_agence_id_foreign')->references(['id'])->on('agences')->onDelete('cascade');
        });
        Schema::table('leave_counters', function (Blueprint $table) {
            $table->foreign(['gent_id'], 'leave_counters_gent_id_foreign')->references(['id'])->on('gents')->onDelete('cascade');
        });
        Schema::table('leave_openings', function (Blueprint $table) {
            $table->foreign(['gent_id'], 'leave_openings_gent_id_foreign')->references(['id'])->on('gents')->onDelete('cascade');
        });
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->foreign(['permission_id'], 'model_has_permissions_permission_id_foreign')->references(['id'])->on('permissions')->onDelete('cascade');
        });
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->foreign(['role_id'], 'model_has_roles_role_id_foreign')->references(['id'])->on('roles')->onDelete('cascade');
        });
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->foreign(['permission_id'], 'role_has_permissions_permission_id_foreign')->references(['id'])->on('permissions')->onDelete('cascade');
            $table->foreign(['role_id'], 'role_has_permissions_role_id_foreign')->references(['id'])->on('roles')->onDelete('cascade');
        });
        Schema::table('villes', function (Blueprint $table) {
            $table->foreign(['country_id'], 'villes_country_id_foreign')->references(['id'])->on('countries')->onDelete('cascade');
        });
        Schema::table('vol_acomptes', function (Blueprint $table) {
            $table->foreign(['reservation_id'], 'vol_acomptes_ibfk_1')->references(['id'])->on('vol_reservations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign(['agent_id'], 'vol_acomptes_ibfk_2')->references(['id'])->on('gents')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('vol_escaliers', function (Blueprint $table) {
            $table->foreign(['air_port_id'], 'escaliers_air_port_id_foreign')->references(['id'])->on('air_ports')->onDelete('cascade');
            $table->foreign(['re_air_port_id'], 'escaliers_re_air_port_id_foreign')->references(['id'])->on('air_ports')->onDelete('cascade');
            $table->foreign(['vol_id'], 'escaliers_vol_id_foreign')->references(['id'])->on('vols')->onDelete('cascade');
        });
        Schema::table('vol_groups', function (Blueprint $table) {
            $table->foreign(['country_id'], 'vol_groups_ibfk_1')->references(['id'])->on('countries');
        });
        Schema::table('vol_passagers', function (Blueprint $table) {
            $table->foreign(['reservation_id'], 'vol_passagers_ibfk_1')->references(['id'])->on('vol_reservations')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('vol_reservation_histories', function (Blueprint $table) {
            $table->foreign(['agent_id'], 'vol_reservation_histories_ibfk_1')->references(['id'])->on('gents');
            $table->foreign(['reservation_id'], 'vol_reservation_histories_ibfk_2')->references(['id'])->on('vol_reservations');
        });
        Schema::table('vol_reservations', function (Blueprint $table) {
            $table->foreign(['vol_id'], 'vol_reservations_ibfk_1')->references(['id'])->on('vols');
            $table->foreign(['gent_id'], 'vol_reservations_ibfk_2')->references(['id'])->on('gents');
            $table->foreign(['vol_retour_id'], 'vol_reservations_ibfk_3')->references(['id'])->on('vols');
        });
        Schema::table('vol_reservations_annules', function (Blueprint $table) {
            $table->foreign(['reservation_id'], 'vol_reservations_annules_ibfk_1')->references(['id'])->on('vol_reservations')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('vols', function (Blueprint $table) {
            $table->foreign(['company_id'], 'vols_ibfk_1')->references(['id'])->on('companies');
            $table->foreign(['ville_id'], 'vols_ibfk_2')->references(['id'])->on('villes');
            $table->foreign(['condition_id'], 'vols_ibfk_3')->references(['id'])->on('conditions');
            $table->foreign(['vol_group_id'], 'vols_ibfk_4')->references(['id'])->on('vol_groups');
            $table->foreign(['vol_group_id'], 'vols_ibfk_5')->references(['id'])->on('vol_groups');
        });
    }

    public function down(): void
    {
        Schema::table('air_ports', function (Blueprint $table) {
            $table->dropForeign('air_ports_ville_id_foreign');
        });
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign('attendances_gent_id_foreign');
        });
        Schema::table('black_notes', function (Blueprint $table) {
            $table->dropForeign('black_notes_ibfk_1');
        });
        Schema::table('block_power_confirmations', function (Blueprint $table) {
            $table->dropForeign('block_power_confirmations_ibfk_1');
            $table->dropForeign('block_power_confirmations_ibfk_2');
        });
        Schema::table('block_power_options', function (Blueprint $table) {
            $table->dropForeign('block_power_options_ibfk_1');
        });
        Schema::table('block_power_passengers', function (Blueprint $table) {
            $table->dropForeign('block_power_passengers_ibfk_1');
        });
        Schema::table('block_power_releases', function (Blueprint $table) {
            $table->dropForeign('block_power_releases_ibfk_1');
            $table->dropForeign('block_power_releases_ibfk_2');
        });
        Schema::table('boat_arrival_ports', function (Blueprint $table) {
            $table->dropForeign('boat_arrival_ports_destination_id_foreign');
        });
        Schema::table('boat_bookings', function (Blueprint $table) {
            $table->dropForeign('boat_bookings_accommodation_id_foreign');
            $table->dropForeign('boat_bookings_arrival_port_id_foreign');
            $table->dropForeign('boat_bookings_company_id_foreign');
            $table->dropForeign('boat_bookings_departure_port_id_foreign');
            $table->dropForeign('boat_bookings_destination_id_foreign');
            $table->dropForeign('boat_bookings_fare_id_foreign');
            $table->dropForeign('boat_bookings_ibfk_1');
            $table->dropForeign('boat_bookings_insurance_id_foreign');
            $table->dropForeign('boat_bookings_vehicle_id_foreign');
            $table->dropForeign('boat_bookings_vehicle_insurance_id_foreign');
            $table->dropForeign('boat_bookings_vehicle_size_id_foreign');
        });
        Schema::table('boat_companies', function (Blueprint $table) {
            $table->dropForeign('boat_companies_destination_id_foreign');
        });
        Schema::table('boat_departure_ports', function (Blueprint $table) {
            $table->dropForeign('boat_departure_ports_destination_id_foreign');
        });
        Schema::table('boat_passengers', function (Blueprint $table) {
            $table->dropForeign('boat_passengers_ibfk_1');
        });
        Schema::table('boat_reservations', function (Blueprint $table) {
            $table->dropForeign('boat_reservations_ibfk_1');
        });
        Schema::table('calendars', function (Blueprint $table) {
            $table->dropForeign('calendars_ibfk_1');
        });
        Schema::table('client_vol_estimates', function (Blueprint $table) {
            $table->dropForeign('client_vol_estimates_ibfk_1');
            $table->dropForeign('client_vol_estimates_ibfk_2');
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign('companies_country_id_foreign');
        });
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropForeign('destinations_country_id_foreign');
        });
        Schema::table('gents', function (Blueprint $table) {
            $table->dropForeign('gents_agence_id_foreign');
        });
        Schema::table('leave_counters', function (Blueprint $table) {
            $table->dropForeign('leave_counters_gent_id_foreign');
        });
        Schema::table('leave_openings', function (Blueprint $table) {
            $table->dropForeign('leave_openings_gent_id_foreign');
        });
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropForeign('model_has_permissions_permission_id_foreign');
        });
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropForeign('model_has_roles_role_id_foreign');
        });
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->dropForeign('role_has_permissions_permission_id_foreign');
            $table->dropForeign('role_has_permissions_role_id_foreign');
        });
        Schema::table('villes', function (Blueprint $table) {
            $table->dropForeign('villes_country_id_foreign');
        });
        Schema::table('vol_acomptes', function (Blueprint $table) {
            $table->dropForeign('vol_acomptes_ibfk_1');
            $table->dropForeign('vol_acomptes_ibfk_2');
        });
        Schema::table('vol_escaliers', function (Blueprint $table) {
            $table->dropForeign('escaliers_air_port_id_foreign');
            $table->dropForeign('escaliers_re_air_port_id_foreign');
            $table->dropForeign('escaliers_vol_id_foreign');
        });
        Schema::table('vol_groups', function (Blueprint $table) {
            $table->dropForeign('vol_groups_ibfk_1');
        });
        Schema::table('vol_passagers', function (Blueprint $table) {
            $table->dropForeign('vol_passagers_ibfk_1');
        });
        Schema::table('vol_reservation_histories', function (Blueprint $table) {
            $table->dropForeign('vol_reservation_histories_ibfk_1');
            $table->dropForeign('vol_reservation_histories_ibfk_2');
        });
        Schema::table('vol_reservations', function (Blueprint $table) {
            $table->dropForeign('vol_reservations_ibfk_1');
            $table->dropForeign('vol_reservations_ibfk_2');
            $table->dropForeign('vol_reservations_ibfk_3');
        });
        Schema::table('vol_reservations_annules', function (Blueprint $table) {
            $table->dropForeign('vol_reservations_annules_ibfk_1');
        });
        Schema::table('vols', function (Blueprint $table) {
            $table->dropForeign('vols_ibfk_1');
            $table->dropForeign('vols_ibfk_2');
            $table->dropForeign('vols_ibfk_3');
            $table->dropForeign('vols_ibfk_4');
            $table->dropForeign('vols_ibfk_5');
        });
    }
};
