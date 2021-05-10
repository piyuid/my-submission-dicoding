import Foundation
// Header
print("Name : Leo Puji Christyanto")
print("Program Menghitung Balok dengan Swift")
print("----------------------------------")
 
// Input Data Balok
print("Masukkanlah lebar balok:"); let widthInput = readLine()
print("Masukkanlah tinggi balok:"); let heightInput = readLine()
print("Masukkanlah panjang balok:"); let lengthInput = readLine()
 
// Rumus Mengubah tipe data string ke double
if let length = Double(lengthInput ?? "0"), let height = Double(heightInput ?? "0"), let width = Double(widthInput ?? "0"){
    // Rumus pengolahan data nya
    let volume = length * height * width
    let surfaceArea = 2 * ((width * length) + (width * height) + (height * length))
    let circumference = 4 * (width + length + height)
    
    print("----------------------------------")
    // Hasil Outputnya
    print("Anda memiliki sebuah balok dengan:")
    print("Lebarnya adalah \(width) cm")
    print("Tingginya adalah \(height) cm")
    print("Panjangnya adalah \(length) cm")
    print("Volemenya adalah \(volume) cm3")
    print("Luas permukaannya adalah \(surfaceArea) cm2")
    print("Kelilingnya adalah \(circumference) cm")
    print("----------------------------------")
} else {
    // Bila input gagal
    print("----------------------------------")
    print("Input tidak valid")
    print("----------------------------------")
}