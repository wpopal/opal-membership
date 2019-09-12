<?php
/* Exit if not defined ABSPATH */
defined( 'ABSPATH' ) || exit();

class Opalmembership_Member_Table extends WP_List_Table {

	/**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items() {
        
        /**
         * Process bulk actions like: delete ...
         */
        $this->process_bulk_action();

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $users = $this->table_data();
        usort( $users, array( &$this, 'sort_data' ) );

        $user_per_page = $this->get_items_per_page( 'users_per_page', 10 );
        $currentPage = $this->get_pagenum();
        $total = count( $users );

        $this->set_pagination_args( array(
            'total_items' => $total,
            'per_page'    => $user_per_page
        ) );

        $users = array_slice( $users, ( ( $currentPage - 1 ) * $user_per_page ), $user_per_page );

        $this->_column_headers = array( $columns, $hidden, $sortable );
        $this->items = $users;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />',
			'username' => esc_html__( 'Username', 'opalmembership' ),
			'email'	=> esc_html__( 'Email', 'opalmembership' ),
            'current_package' => esc_html__( 'Current Package', 'opalmembership' ),
            'lastest_payment' => esc_html__( 'Lastest Payment', 'opalmembership' ),
			'registered_at' => esc_html__( 'Activated At', 'opalmembership' ),
			'expired_at' => esc_html__( 'Expired At', 'opalmembership' ),
			'status' => esc_html__( 'Member Status', 'opalmembership' )
        );

        return apply_filters( 'opalmembership_member_user_columns', $columns );
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns() {
        return apply_filters( 'opalmembership_hidden_columns', array() );
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns() {
        return apply_filters( 'opalmembership_member_user_sortable_columns', array(
                'username'          => array( 'username', false ),
                'email'             => array( 'email', false ),
                'registered_at'     => array( 'registered_at', false ),
                'expired_at'        => array( 'expired_at', false ),
                'lastest_payment'   => array( 'lastest_payment', false ),
            ) );
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data() {
    	$users = get_users( apply_filters( 'opalmembership_member_table_arguments', array(
    			'meta_key'     => OPALMEMBERSHIP_USER_PREFIX_.'package_id',
                'meta_value'   => '0',
                'meta_compare' => '>',
    		) ) );
        $data = array();

        foreach ( $users as $user ) {
        	$data[] = array(
        			'id'                 => $user->ID,
        			'username'           => $user->data->user_login,
        			'email'              => $user->data->user_email,
                    'current_package'    => (int)get_user_meta( $user->ID, OPALMEMBERSHIP_USER_PREFIX_.'package_id', true ),
                    'lastest_payment'   => (int)get_user_meta( $user->ID, OPALMEMBERSHIP_USER_PREFIX_.'payment_id', true ),
        			'registered_at'      => get_user_meta( $user->ID, OPALMEMBERSHIP_USER_PREFIX_.'package_activation', true ),
        			'expired_at'         => get_user_meta( $user->ID, OPALMEMBERSHIP_USER_PREFIX_.'package_expired', true ),
        			'status'             => ''
        		);
        }

        return apply_filters( 'opalmembership_member_user_table_data', $data );
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    public function column_cb( $item ) {
        return sprintf(
                '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
        );
    }

    /**
     * Method for name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    public function column_username( $item ) {

        $delete_nonce = wp_create_nonce( 'opalmembership-delete-user' );
        $title = '<strong><a href="'. get_edit_user_link( $item['id'] ) .'">' . $item['username'] . '</a></strong>';

        $actions = array(
                'delete' => sprintf( '<a href="?page=%s&action=%s&user=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce, esc_html__( 'Delete', 'opalmembership' ) ),
                'edit'  => sprintf( '<a href="%s">%s</a>', get_edit_user_link( $item['id'] ), esc_html__( 'Edit', 'opalmembership' ) )
            );

        $actions = apply_filters( 'opalmembership_username_row_actions', $actions, $item );
        return apply_filters( 'opalmembership_member_username_column_username', $title . $this->row_actions( $actions ), $item );
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name ) {
    	$package_activation = $item['registered_at'];
    	$package_expired = $item['expired_at'];
        switch( $column_name ) {
            case 'id':
            case 'username':
            case 'email':
                return $item[ $column_name ];
               	break;
            case 'lastest_payment':
                return $item[$column_name] ? sprintf( '<a href="%s">#%s<a/>', get_edit_post_link( $item[$column_name] ), $item[$column_name] ) : esc_html__( 'No Payment', 'opalmembership' );
                break;
            case 'registered_at':
            case 'expired_at':
            	return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item[$column_name] ) );
            	break;
            case 'current_package':
                    return sprintf( '<a href="%s">%s<a/>', get_edit_post_link( $item['current_package'] ), get_the_title( $item['current_package'] ) );
                break;
            case 'status':
            	if ( opalmembership_is_membership_valid( $item['id'] ) ) {
            		return '<span class="member-status activated">'.esc_html__( 'Activated', 'opalmembership' ).'</span>';
            	} else {
            		return '<span class="member-status expired">'.esc_html__( 'Expired', 'opalmembership' ).'</span>';
            	}
            	break;
            default:
                return '';
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b ) {
        // Set defaults
        $orderby = 'username';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if( ! empty( $_GET['orderby'] ) ) {
            $orderby = sanitize_text_field( $_GET['orderby'] );
        }

        // If order is set use this as the order
        if( ! empty( $_GET['order'] ) ) {
            $order = sanitize_text_field( $_GET['order'] );
        }

        $result = strnatcmp( $a[$orderby], $b[$orderby] );

        if( strtolower( $order ) === 'asc' ) {
            return $result;
        }

        return apply_filters( 'opalmembership_member_user_sort_data', - $result );
    }

    /**
     * Process bulk action. In this case only delete checked users
     */
    public function process_bulk_action() {

        if ( 'delete' === $this->current_action() ) {

            $nonce = sanitize_text_field( $_REQUEST['_wpnonce'] );

            if ( ! wp_verify_nonce( $nonce, 'opalmembership-delete-user' ) ) {
                wp_die( esc_html__( 'System could not delete user.', 'opalmembership' ) );
            } else {
                $this->delete_user( absint( $_GET['user'] ) );
            }
        }

        /**
         * delete multi users
         */
        if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'bulk-delete' ) || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] === 'bulk-delete' ) ) {

            $delete_ids = esc_sql( $_REQUEST['bulk-delete'] );

            foreach ( $delete_ids as $id ) {
                $this->delete_user( $id );
            }

        }
    }

    /**
     * Delete single user
     * @user_id int
     */
    public function delete_user( $user_id = null ) {
        if ( $user_id ) {
            wp_delete_user( $user_id );
        }
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = array(
                'bulk-delete' => esc_html__( 'Delete', 'opalmembership' )
            );

        return apply_filters( 'opalmembership_member_user_bulk_actions', $actions );
    }

}