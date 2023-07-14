<?php

namespace Seeders\InitialSeeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(AddressBookTableSeeder::class);
        $this->call(AddressFormatTableSeeder::class);
        $this->call(AdminTableSeeder::class);
        $this->call(AdminMenusTableSeeder::class);
        $this->call(AdminPagesTableSeeder::class);
        $this->call(AdminPagesToProfilesTableSeeder::class);
        $this->call(AdminProfilesTableSeeder::class);
        $this->call(BannersTableSeeder::class);
        $this->call(BannersHistoryTableSeeder::class);
        $this->call(CategoriesTableSeeder::class);
        $this->call(CategoriesDescriptionTableSeeder::class);
        $this->call(ConfigurationTableSeeder::class);
        $this->call(ConfigurationGroupTableSeeder::class);
        $this->call(CountProductViewsTableSeeder::class);
        $this->call(CounterTableSeeder::class);
        $this->call(CounterHistoryTableSeeder::class);
        $this->call(CountriesTableSeeder::class);
        $this->call(CurrenciesTableSeeder::class);
        $this->call(CustomersTableSeeder::class);
        $this->call(CustomersInfoTableSeeder::class);
        $this->call(EzpagesTableSeeder::class);
        $this->call(EzpagesContentTableSeeder::class);
        $this->call(FeaturedTableSeeder::class);
        $this->call(GeoZonesTableSeeder::class);
        $this->call(GetTermsToFilterTableSeeder::class);
        $this->call(GroupPricingTableSeeder::class);
        $this->call(LanguagesTableSeeder::class);
        $this->call(LayoutBoxesTableSeeder::class);
        $this->call(ManufacturersTableSeeder::class);
        $this->call(ManufacturersInfoTableSeeder::class);
        $this->call(MediaClipsTableSeeder::class);
        $this->call(MediaManagerTableSeeder::class);
        $this->call(MediaToProductsTableSeeder::class);
        $this->call(MediaTypesTableSeeder::class);
        $this->call(MusicGenreTableSeeder::class);
        $this->call(OrdersStatusTableSeeder::class);
        $this->call(PaypalPaymentStatusTableSeeder::class);
        $this->call(ProductMusicExtraTableSeeder::class);
        $this->call(ProductTypeLayoutTableSeeder::class);
        $this->call(ProductTypesTableSeeder::class);
        $this->call(ProductTypesToCategoryTableSeeder::class);
        $this->call(ProductsTableSeeder::class);
        $this->call(ProductsAttributesTableSeeder::class);
        $this->call(ProductsAttributesDownloadTableSeeder::class);
        $this->call(ProductsDescriptionTableSeeder::class);
        $this->call(ProductsDiscountQuantityTableSeeder::class);
        $this->call(ProductsOptionsTableSeeder::class);
        $this->call(ProductsOptionsTypesTableSeeder::class);
        $this->call(ProductsOptionsValuesTableSeeder::class);
        $this->call(ProductsOptionsValuesToProductsOptionsTableSeeder::class);
        $this->call(ProductsToCategoriesTableSeeder::class);
        $this->call(ProjectVersionTableSeeder::class);
        $this->call(ProjectVersionHistoryTableSeeder::class);
        $this->call(QueryBuilderTableSeeder::class);
        $this->call(RecordArtistsTableSeeder::class);
        $this->call(RecordArtistsInfoTableSeeder::class);
        $this->call(RecordCompanyTableSeeder::class);
        $this->call(RecordCompanyInfoTableSeeder::class);
        $this->call(ReviewsTableSeeder::class);
        $this->call(ReviewsDescriptionTableSeeder::class);
        $this->call(SalemakerSalesTableSeeder::class);
        $this->call(SpecialsTableSeeder::class);
        $this->call(TaxClassTableSeeder::class);
        $this->call(TaxRatesTableSeeder::class);
        $this->call(TemplateSelectTableSeeder::class);
        $this->call(ZonesTableSeeder::class);
        $this->call(ZonesToGeoZonesTableSeeder::class);
    }
}
