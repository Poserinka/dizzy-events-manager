class Deactivator {

    public static function deactivate(): void {

        Scheduler::clear();

        flush_rewrite_rules();

    }

}