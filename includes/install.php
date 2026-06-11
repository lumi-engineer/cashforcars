<?php
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
class Install {
    private $wpdb;
    private $charset_collate;
    private $years_table ;
    private $makes_table;
    private $models_table;
    private $types_table_name;
    private $types_data_table_name;
    private $users_table;
    private $quotes_table;
    private $quotes_data_table;
    public function __construct() {
        global $wpdb;

        $this->wpdb = $wpdb;
        $this->charset_collate = '';
        $this->years_table = $this->wpdb->prefix . 'df_ci_years';
        $this->makes_table = $this->wpdb->prefix . 'df_ci_makes';
        $this->models_table = $this->wpdb->prefix . 'df_ci_models';
        $this->types_table_name = $this->wpdb->prefix . 'df_ci_types';
        $this->types_data_table_name = $this->wpdb->prefix . 'df_ci_types_data';
        $this->quotes_table = $this->wpdb->prefix  . 'df_ci_quotes';
        $this->quotes_data_table = $this->wpdb->prefix . 'df_ci_quote_data';
        $this->users_table = $this->wpdb->prefix . 'df_ci_users';
        if (!empty($this->wpdb->charset)) {
            $this->charset_collate .= "DEFAULT CHARACTER SET {$this->wpdb->charset}";
        }
        if (!empty($this->wpdb->collate)) {
            $this->charset_collate .= " COLLATE {$this->wpdb->collate}";
        }

        $this->createTables();
        $this->migrateQuoteWorkflow();
        $this->populateYearsTable();
        $this->insertCarDataFromCsv();
        $this->populateTypesTable();
        $this->populateTypesDataTable();
    }

    private function createTables() {
        $this->createYearsTable();
        $this->createMakesTable();
        $this->createModelsTable();
        $this->createTypesTable();
        $this->createTypesDataTable();
        $this->createQuotesTable();
        $this->createQuotesDataTable();
        $this->createUsersTable();
    }

    private function migrateQuoteWorkflow() {
        require_once CI_INCLUDES . '/helpers/QuoteStatus.php';
        CI_Quote_Status::migrate_legacy_rows( $this->wpdb, $this->quotes_table );
    }

    private function createYearsTable() {
        $sql_years = "CREATE TABLE {$this->years_table} (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `year` VARCHAR(4) NOT NULL,
            PRIMARY KEY (`id`)
        ) {$this->charset_collate}";

        dbDelta($sql_years);
    }

   private function createMakesTable() {
        $table_name = $this->makes_table;
        $charset_collate = $this->charset_collate;
        $sql = "CREATE TABLE $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            make VARCHAR(255) NOT NULL,
            description TEXT,
            PRIMARY KEY (id)
        ) $charset_collate;";
    
        dbDelta($sql);
    }

   private function createModelsTable() {
        $table_name = $this->models_table;
        $charset_collate = $this->charset_collate;
        $sql = "CREATE TABLE $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            make_id INT NOT NULL,
            model VARCHAR(255) NOT NULL,
            year INT NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (make_id) REFERENCES {$this->makes_table}(id)
        ) $charset_collate;";
    
        dbDelta($sql);
    }
    private function createTypesTable()
    {
        $type_table_name = $this->types_table_name;
        $sql = "CREATE TABLE $type_table_name (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type_name VARCHAR(255) NOT NULL
        ) $this->charset_collate;";
        dbDelta($sql);
    }
    private function createTypesDataTable()
    {
        $data_table_name = $this->types_data_table_name;
        $sql = "CREATE TABLE $data_table_name (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            type_id INT,
            FOREIGN KEY (type_id) REFERENCES $this->types_table_name(id)
        ) $this->charset_collate;";
        dbDelta($sql);
    }
    function populateTypesTable() {
        $data = [ "vehicle_type", "damage_location", "damage_type", "cause_of_loss"];
        foreach ($data as $entry) {
            $entry = sanitize_text_field($entry);
            // Check if the entry already exists in the table
            $entry_exists = $this->wpdb->get_row("SELECT * FROM $this->types_table_name WHERE type_name = '$entry'");
            // If the entry doesn't exist, insert it
            if (!$entry_exists) {
                $this->wpdb->insert(
                    $this->types_table_name,
                    array(
                        'type_name' => $entry,
                    )
                );
            }
        }
    }
    private function createQuotesTable()
    {        
        $sql = "CREATE TABLE $this->quotes_table  (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            transaction_id VARCHAR(255) NOT NULL,
            claim_number VARCHAR(255) NOT NULL,
            stock_number VARCHAR(255) NULL,
            email VARCHAR(255) NOT NULL,
            status INT DEFAULT 1 NOT NULL,
            data_proquote LONGTEXT NULL,
            data_assignment LONGTEXT NULL,
            images LONGTEXT NULL,
            title_images LONGTEXT NULL,
            car_images LONGTEXT NULL,
            images_status VARCHAR(20) DEFAULT 'none' NOT NULL,
            title_review VARCHAR(20) DEFAULT 'none' NOT NULL,
            car_review VARCHAR(20) DEFAULT 'none' NOT NULL,
            is_deleted BOOLEAN DEFAULT 0 NOT NULL,
            PRIMARY KEY  (id)
        ) $this->charset_collate;";  
        dbDelta($sql);      
    }
    private function createQuotesDataTable() 
    {
        $sql = "CREATE TABLE $this->quotes_data_table  (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            qid VARCHAR(255) NOT NULL,
            quote_id VARCHAR(255) NOT NULL,
            status_code VARCHAR(50) NOT NULL,
            high_quote DECIMAL(10, 2) NOT NULL,
            low_quote DECIMAL(10, 2) NOT NULL,
            pro_quote DECIMAL(10, 2) NOT NULL,
            number_of_lots INT NOT NULL,
            PRIMARY KEY (id)
        ) $this->charset_collate;";
            dbDelta($sql);
    }
    // private function insertCarDataFromCsv() {
    //     $makes_table = $this->makes_table;
    //     $models_table = $this->models_table;
    //     $years_table = $this->years_table;
    
    //     $csv_path = CI_DATA . '/IVX3_make_model_year.csv';
    //     $csv_data = array_map('str_getcsv', file($csv_path));
    //     // Remove the first row (header) from CSV data
    //     array_shift($csv_data);
    
    //     foreach ($csv_data as $row) {
    //         $model_year = intval(trim($row[0]));
    //         $vehicle_make = trim($row[1]);
    //         $make_full_title = trim($row[2]);
    //         $vehicle_model = trim($row[3]);
    
    //         // Check if the make already exists
    //         $make_id = $this->wpdb->get_var($this->wpdb->prepare("SELECT id FROM $makes_table WHERE make = %s", $vehicle_make));
    //         if (!$make_id) {
    //             // Insert the make if it does not exist
    //             $this->wpdb->insert($makes_table, array('make' => $vehicle_make, 'description' => $make_full_title));
    //             $make_id = $this->wpdb->insert_id;
    //         }
    
    //         // Check if the model already exists for the given make and year
    //         $model_id = $this->wpdb->get_var($this->wpdb->prepare("SELECT id FROM $models_table WHERE make_id = %d AND model = %s AND year = %d", $make_id, $vehicle_model, $model_year));
    //         if (!$model_id) {
    //             // Insert the model if it does not exist
    //             $this->wpdb->insert($models_table, array('make_id' => $make_id, 'year' => $model_year, 'model' => $vehicle_model));
    //         }
    
    //         // Check if the year already exists
    //         $existing_year = $this->wpdb->get_var($this->wpdb->prepare("SELECT year FROM $years_table WHERE year = %d", $model_year));
    //         if ($existing_year != $model_year) {
    //             // Insert the year if it does not exist
    //             $this->wpdb->insert($years_table, array('year' => $model_year));
    //         }
    //     }
    // }
    private function insertCarDataFromCsv() {
    global $wpdb;

    $makes_table  = $this->makes_table;
    $models_table = $this->models_table;
    $years_table  = $this->years_table;

    $csv_path = CI_DATA . '/IVX3_make_model_year.csv';

    if (!file_exists($csv_path)) {
        return;
    }

    // Cache existing makes and years to avoid repeated queries
    $makes_cache = $wpdb->get_results(
        "SELECT id, make FROM {$makes_table}",
        OBJECT_K
    );

    $years_cache = $wpdb->get_col(
        "SELECT year FROM {$years_table}"
    );
    $years_cache = array_flip($years_cache);

    if (($handle = fopen($csv_path, 'r')) === false) {
        return;
    }

    // Skip header row
    fgetcsv($handle);

    while (($row = fgetcsv($handle)) !== false) {

        if (count($row) < 4) {
            continue;
        }

        $model_year       = (int) trim($row[0]);
        $vehicle_make     = trim($row[1]);
        $make_full_title  = trim($row[2]);
        $vehicle_model    = trim($row[3]);

        if (!$model_year || !$vehicle_make || !$vehicle_model) {
            continue;
        }

        /**
         * MAKE
         */
        if (isset($makes_cache[$vehicle_make])) {
            $make_id = $makes_cache[$vehicle_make]->id;
        } else {
            $wpdb->insert(
                $makes_table,
                [
                    'make'        => $vehicle_make,
                    'description' => $make_full_title,
                ],
                ['%s', '%s']
            );

            $make_id = $wpdb->insert_id;

            $makes_cache[$vehicle_make] = (object) [
                'id' => $make_id,
            ];
        }

        /**
         * MODEL
         * Rely on DB unique index to prevent duplicates
         */
        $wpdb->query(
            $wpdb->prepare(
                "INSERT IGNORE INTO {$models_table} (make_id, year, model)
                 VALUES (%d, %d, %s)",
                $make_id,
                $model_year,
                $vehicle_model
            )
        );

        /**
         * YEAR
         */
        if (!isset($years_cache[$model_year])) {
            $wpdb->insert(
                $years_table,
                ['year' => $model_year],
                ['%d']
            );
            $years_cache[$model_year] = true;
        }
    }

    fclose($handle);
}

    private function populateTypesDataTable()
    {
        $types = [ "vehicle_type", "damage_location", "damage_type"];
        foreach($types as $type)
        {
            if($type == "vehicle_type")
            {
                $type_id = $this->getTypeIdByValue($type);
                $data = array(
                    'A' => 'ATV',
                    'C' => 'MOTORCYCLE',
                    'D' => 'DIRT BIKE',
                    'E' => 'INDUSTRIAL EQUIPMENT',
                    'H' => 'OTHER GOODS',
                    'I' => 'INSPECTION',
                    'J' => 'JET SKI',
                    'K' => 'MEDIUM DUTY/BOX TRUCKS',
                    'L' => 'TRAILERS',
                    'M' => 'BOAT',
                    'R' => 'RECREATIONAL VEHICLE (RV)',
                    'S' => 'SNOWMOBILE',
                    'T' => 'TRANSPORT',
                    'U' => 'HEAVY DUTY TRUCKS',
                    'V' => 'AUTOMOBILE'
                );
            }
            elseif($type == "damage_location")
            {
                $type_id = $this->getTypeIdByValue($type);
                $data = array(
                    'AO' => 'ALL OVER',
                    'BC' => 'BIOHAZARD/CHEMICAL',
                    'BE' => 'BURN - ENGINE',
                    'BI' => 'BURN - INTERIOR',
                    'BN' => 'BURN',
                    'DH' => 'DAMAGE HISTORY',
                    'FD' => 'FRAME DAMAGE',
                    'FR' => 'FRONT END',
                    'HL' => 'HAIL',
                    'MC' => 'MECHANICAL',
                    'MN' => 'MINOR DENT/SCRATCHES',
                    'NO' => 'NORMAL WEAR',
                    'PV' => 'PARTIAL REPAIR',
                    'RJ' => 'REJECTED REPAIR',
                    'RO' => 'ROLLOVER',
                    'RR' => 'REAR END',
                    'SD' => 'SIDE',
                    'ST' => 'STRIPPED',
                    'TP' => 'TOP/ROOF',
                    'UK' => 'UNKNOWN',
                    'UN' => 'UNDERCARRIAGE',
                    'VI' => 'MISSING/ALTERED VIN',
                    'VN' => 'VANDALISM',
                    'VP' => 'REPLACED VIN',
                    'WA' => 'WATER/FLOOD'
                );
            }
            elseif($type == "damage_type")
            {
                $type_id = $this->getTypeIdByValue($type);
                $data = array(
                    'B' => 'BURN',
                    'C' => 'COLLISION',
                    'D' => 'DONATION',
                    'E' => 'FLEET/LEASE',
                    'H' => 'HAIL',
                    'I' => 'WIND',
                    'K' => 'CATASTROPHE',
                    'L' => 'RENTAL',
                    'M' => 'OTHER COMPREHENSIVE',
                    'N' => 'CDS VEHICLE',
                    'O' => 'IMPOUND',
                    'P' => 'PROPERTY DAMAGE',
                    'R' => 'REPOSSESSION',
                    'S' => 'VANDALISM',
                    'T' => 'THEFT',
                    'U' => 'UNINSURED MOTORISTS',
                    'W' => 'WATER/FLOOD',
                    'X' => 'DEALER CONSIGNMENT',
                    'Y' => 'PUBLIC CONSIGNMENT'
                );
            }
            // Iterate through the data and insert if not already exists
            foreach ($data as $code => $name) {
                $existing_data = $this->wpdb->get_row("SELECT * FROM $this->types_data_table_name WHERE type_id = '$type_id' AND code = '$code'");
                if (!$existing_data) {
                    $this->wpdb->insert(
                        $this->types_data_table_name,
                        array(
                            'code' => $code,
                            'name' => $name,
                            'type_id' => $type_id
                        ),
                        array('%s', '%s')
                    );
                }
            }
        }

    }
    function getTypeIdByValue($type_value) {
        $type_id = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT id FROM $this->types_table_name WHERE type_name = %s",
                $type_value
            )
            );
        return $type_id;
    }
    private function createUsersTable() {
        $sql_years = "CREATE TABLE {$this->users_table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL
        ) {$this->charset_collate}";

        dbDelta($sql_years);
    }
}

