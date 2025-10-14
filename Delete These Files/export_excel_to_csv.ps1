# PowerShell script to export Excel sheets to CSV
$excelFile = "C:\laragon\www\CREAMS\documentation\UAT FILES\CREAMS_User Acceptance Testing.xlsx"
$outputDir = "C:\laragon\www\CREAMS\documentation\UAT FILES\"

# Create Excel COM object
$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$excel.DisplayAlerts = $false

try {
    # Open workbook
    $workbook = $excel.Workbooks.Open($excelFile)

    Write-Host "Available sheets:"
    foreach ($sheet in $workbook.Worksheets) {
        Write-Host "  - $($sheet.Name) (Rows: $($sheet.UsedRange.Rows.Count), Cols: $($sheet.UsedRange.Columns.Count))"
    }

    # Export each sheet to CSV
    $sheetIndex = 1
    foreach ($sheet in $workbook.Worksheets) {
        $csvFile = Join-Path $outputDir "UAT_Sheet_$sheetIndex`_$($sheet.Name).csv"
        $sheet.SaveAs($csvFile, 6)  # 6 = xlCSV
        Write-Host "Exported: $csvFile"
        $sheetIndex++
    }

    $workbook.Close($false)
    Write-Host "Export complete!"
}
catch {
    Write-Host "Error: $_"
}
finally {
    $excel.Quit()
    [System.Runtime.Interopservices.Marshal]::ReleaseComObject($excel) | Out-Null
}
